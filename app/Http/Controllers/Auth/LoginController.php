<?php

namespace App\Http\Controllers\Auth;
use Exception;
use Carbon\Carbon;
use App\Traits\Otp;
use App\Models\Cart;
use App\Models\User;
use App\Models\Compare;
use App\Models\Profile;
use App\Traits\Notification;
use Illuminate\Http\Request;
use App\Models\SocialProvider;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Modules\FrontendCMS\Entities\LoginPage;
use Illuminate\Validation\ValidationException;
use Modules\UserActivityLog\Traits\LogActivity;
use Modules\SidebarManager\Entities\Backendmenu;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Modules\SidebarManager\Entities\BackendmenuUser;
use Modules\GeneralSetting\Entities\EmailTemplateType;
use Modules\GeneralSetting\Entities\NotificationSetting;
use Modules\GeneralSetting\Entities\UserNotificationSetting;

class LoginController extends Controller
{
    use AuthenticatesUsers, Notification, Otp;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    // protected $redirectTo = RouteServiceProvider::HOME;

    protected function redirectTo()
    {
        Toastr::success(__('auth.logged_in_successfully'), __('common.success'));
        if(session()->has('from_checkout')){
            $next_url = session()->get('from_checkout');
            session()->forget('from_checkout');
            return $next_url;
        }
        if (auth()->user()->role->type == 'superadmin' || auth()->user()->role->type == 'admin' || auth()->user()->role->type == 'staff') {
            return '/admin-dashboard';
        } elseif (auth()->user()->role->type == 'seller') {
            return '/seller/dashboard';
        }
        return '/profile/dashboard';
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['guest'])->except('logout');
    }

    public function showLoginForm()
    {
        if(url()->previous() == url('/checkout') || url()->previous() == url('/checkout?checkout_type=YnV5X2l0X25vdw==')){
            session()->put('from_checkout',url()->previous());
        }
        $seller = User::whereHas('role', function($q){
            return $q->where('type', 'seller');
        })->first();
        $seller_email = null;
        $customer_email = null;
        if($seller){
            $seller_email = $seller->email;
        }
        $customer = User::whereHas('role', function($q){
            return $q->where('type', 'customer');
        })->first();
        if($customer){
            $customer_email = $customer->email;
        }
        $loginPageInfo = LoginPage::findOrFail(2);
        return view(theme('auth.login'), compact('seller_email', 'customer_email', 'loginPageInfo'));
    }

    // start for admin login
    public function showAdminLoginForm(){
        $admin_email = User::whereHas('role', function($q){
            return $q->where('type', 'superadmin');
        })->first()->email;

        $loginPageInfo = LoginPage::findOrFail(1);

        return view(theme('auth.admin_login'), compact('admin_email', 'loginPageInfo'));
    }

    public function adminLogin(Request $request){
        if (env('NOCAPTCHA_FOR_LOGIN') == "true" && app('theme')->folder_path == 'amazy') {
            $request->validate([
                'g-recaptcha-response' => 'required',
            ],[
                'g-recaptcha-response.required' => 'The google recaptcha field is required.',
            ]);
        }

        $user = null;
        $user = User::where('email', $request->login)->where('is_active', 1)->whereHas('role', function($query){
            return $query->where('type', 'superadmin')->orWhere('type', 'admin')->orWhere('type', 'staff');
        })->first();
        if(!$user){
            $user = User::where('username', $request->login)->where('is_active', 1)->whereHas('role', function($query){
                return $query->where('type', 'superadmin')->orWhere('type', 'admin')->orWhere('type', 'staff');
            })->first();
        }

        if($user){
            if (config('app.sync') && $request->auto_login == "true"){
                return $this->loginDone($request, $user);
            }else{
                return $this->sendOtpAndCheck($request, null);
            }
        }else{
            throw ValidationException::withMessages([
                "email" => __('auth.failed'),
            ]);
        }
    }
    // end for admin login

    // start seller login
    public function showSellerLoginForm(){
        $seller = User::whereHas('role', function($q){
            return $q->where('type', 'seller');
        })->first();
        $seller_email = null;
        if($seller){
            $seller_email = $seller->email;
        }

        $loginPageInfo = LoginPage::findOrFail(3);

        if(app('theme')->folder_path == 'amazy'){
            return view('multivendor::auth.amazy.seller_login', compact('seller_email', 'loginPageInfo'));
        }else{
            return view('multivendor::auth.default.seller_login', compact('seller_email', 'loginPageInfo'));
        }
    }

    public function sellerLogin(Request $request){
        if (env('NOCAPTCHA_FOR_LOGIN') == "true" && app('theme')->folder_path == 'amazy') {
            $request->validate([
                'g-recaptcha-response' => 'required',
            ],[
                'g-recaptcha-response.required' => 'The google recaptcha field is required.',
            ]);
        }
        $user = null;
        $user = User::where('email', $request->login)->where('is_active', 1)->whereHas('role', function($query){
            return $query->where('type', 'seller');
        })->first();
        if(!$user){
            $user = User::where('username', $request->login)->where('is_active', 1)->whereHas('role', function($query){
                return $query->where('type', 'seller');
            })->first();
        }

        if($user){
            $this->setupSidebar($user);
            if (config('app.sync') && $request->auto_login == "true"){

                return $this->loginDone($request, $user);
            }else{
                return $this->sendOtpAndCheck($request, null);
            }
        }else{
            throw ValidationException::withMessages([
                "email" => __('auth.failed')
            ]);
        }
    }

    // end seller login

    public function username()
    {
        $login = request()->input('login');
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $fieldType = 'email';
        } elseif (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $fieldType = 'username';
        }

        request()->merge([$fieldType  =>  $login]);
        return $fieldType;
    }

    public function login(Request $request)
    {
        $user = null;

        $check_user = User::where('email', $request->login)->where('is_active', 1)->whereHas('role', function($q){
            return $q->where('type', 'customer');
        })->first();
        if(!$check_user){
            $check_user = User::where('username', $request->login)->where('is_active', 1)->whereHas('role', function($q){
                return $q->where('type', 'customer');
            })->first();
        }

        if($check_user){
            if (config('app.sync') && $request->auto_login == "true"){
                $user = $check_user;
            } else{
                $this->validateLogin($request);
                return $this->sendOtpAndCheck($request, $user);
            }
            return $this->loginDone($request, $user);
        }else{
            throw ValidationException::withMessages([
                "email" => __('auth.failed')
            ]);
        }


    }
    protected function validateLogin(Request $request)
    {
        if (env('NOCAPTCHA_FOR_LOGIN') == "true" && app('theme')->folder_path == 'amazy') {
            $g_recaptcha = 'required';
        }else{
            $g_recaptcha = 'nullable';
        }
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string|min:8',
            'g-recaptcha-response' => $g_recaptcha,
        ],[
            'g-recaptcha-response.required' => 'The google recaptcha field is required.',
        ]);
    }

    public function sendOtpAndCheck($request, $user){

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }
        if (isModuleActive('Otp') && otp_configuration('otp_on_login')) {
            $userData = User::where('email', $request->login)->where('is_active', 1)->first();
            if(!$userData){
                $userData = User::where('username', $request->login)->where('is_active', 1)->first();
            }
            if(!$userData || !Hash::check($request->password,$userData->password)){
                throw ValidationException::withMessages([
                    'email' => __('auth.failed')
                ]);
            }
            try {
                if (!$this->sendLoginOtp($request)) {
                    Toastr::error(__('otp.something_wrong_on_otp_send'), __('common.error'));
                    return back();
                }
                return view(theme('auth.login_otp'), compact('request'));
            } catch (Exception $e) {
                LogActivity::errorLog($e->getMessage());
                Toastr::error(__('otp.something_wrong_on_otp_send'), __('common.error'));
                return back();
            }
        }
        return $this->loginDone($request, $user);
    }

    public function loginDone($request, $user = null){
        $prev_session_id = session()->getId();
        $buy_it_now = session()->get('buy_it_now');
        if($user){
            $this->guard()->login($user);
            $loged_in = true;
        }else{
            $loged_in = $this->attemptLogin($request);
        }
        if ($loged_in) {


            if(!isModuleActive('MultiVendor') && auth()->user()->role->type == 'seller'){
                auth()->logout();
                Session::flush();
                Toastr::error(__('common.you_have_been_disabled'), __('common.error'));
                return redirect()->route('login');
            }
            if (auth()->user()->is_active == 0) {
                auth()->logout();
                Session::flush();
                Toastr::error(__('common.you_have_been_disabled'), __('common.error'));
                return redirect()->route('login');
            }
            if(auth()->user()->role->type != 'superadmin' && auth()->user()->role->type != 'admin' && auth()->user()->role->type != 'staff' || isModuleActive('MultiVendor')){
                $this->dataUpdateWhenLogin($prev_session_id, $buy_it_now);
            }
            if (Session::has('compare')) {
                $compare = collect();
                foreach (Session::get('compare') as $key => $compareItem) {
                    $compareData = Compare::where('product_sku_id', $compareItem['product_sku_id'])->where('customer_id', auth()->user()->id)->first();
                    if ($compareData) {
                    } else {
                        Compare::create([
                            'product_sku_id' => $compareItem['product_sku_id'],
                            'data_type' => $compareItem['data_type'],
                            'product_type' => $compareItem['product_type'],
                            'customer_id' => auth()->user()->id,
                        ]);
                    }
                }
            }
            $this->setupSidebar(auth()->user());
            LogActivity::addLoginLog(Auth::user()->id, Auth::user()->first_name . ' - logged in at : ' . Carbon::now());
            Toastr::success(__('auth.logged_in_successfully'), __('common.success'));
            return $this->sendLoginResponse($request);
        }
        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);
        return $this->sendFailedLoginResponse($request);
    }
    public function logout(Request $request)
    {
        LogActivity::addLogoutLog(Auth::user()->id, Auth::user()->first_name . ' - logged out at : ' . Carbon::now());
        $this->guard()->logout();
        $request->session()->flush();
        $request->session()->regenerate();
        Toastr::success(__('auth.logout_successfully'), __('common.success'));
        Session::put('ip', request()->ip());
        return redirect('/');
    }
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }
    private function dataUpdateWhenLogin($prev_session_id, $buy_it_now){
        if($buy_it_now == 'yes'){
            session()->put('but_it_now', 'yes');
        }
        $carts = Cart::where('session_id', $prev_session_id)->get();
        if ($carts->count()) {
            foreach ($carts as $key => $cartItem) {
                $cartData = Cart::where('product_id', $cartItem->product_id)->where('user_id', auth()->id())->where('seller_id', $cartItem->seller_id)->where('shipping_method_id', $cartItem->shipping_method_id)->where('product_type',$cartItem->product_type)->first();
                if ($cartData) {
                    $cartData->update([
                        'qty' => $cartItem->qty,
                        'total_price' => $cartItem->total_price,
                        'is_select' => 1
                    ]);
                    $cartItem->delete();
                } else {
                    $cartItem->update([
                        'user_id' => auth()->id(),
                        'session_id' => null
                    ]);
                }
            }
        }
    }
    public function handleProviderCallback($provider)
    {
        try {
            $user = Socialite::driver($provider)->user();
            $findUser = SocialProvider::whereProviderId($user->getId())->whereProviderName($provider)->first();
            $prev_session_id = session()->getId();
            $buy_it_now = session()->get('buy_it_now');
            if ($findUser) {
                $user = User::whereId($findUser->user_id)->first();
                if($user && $user->is_verified == 0){
                    $user->update([
                        'is_verified' => 1
                    ]);
                }
                Auth::login($user, true);
                if (auth()->user()->is_active == 0) {
                    auth()->logout();
                    Session::flush();
                    Toastr::error(__('common.you_have_been_disabled'), __('common.error'));
                    return redirect()->route('login');
                }
                $this->dataUpdateWhenLogin($prev_session_id, $buy_it_now);
                if (Session::has('compare')) {
                    $compare = collect();
                    foreach (Session::get('compare') as $key => $compareItem) {
                        $compareData = Compare::where('product_sku_id', $compareItem['product_sku_id'])->where('customer_id', auth()->user()->id)->first();
                        if ($compareData) {
                        } else {
                            Compare::create([
                                'product_sku_id' => $compareItem['product_sku_id'],
                                'data_type' => $compareItem['data_type'],
                                'product_type' => $compareItem['product_type'],
                                'customer_id' => auth()->user()->id,
                            ]);
                        }
                    }
                }
                return redirect($this->redirectTo());
            } else {
                $exsist = User::where('email', $user->email)->first();
                if(!$exsist){
                    $newUser = User::create([
                        'first_name' => $user->name,
                        'email' => $user->email,
                        'password' => Hash::make("verystrongpass1234"),
                        'role_id' => 4,
                        'is_verified' => 1,
                        'currency_id' => app('general_setting')->currency,
                        'lang_code' => app('general_setting')->language_code,
                        'currency_code' => app('general_setting')->currency_code,
                    ]);
                    // User Notification Setting Create
                    (new UserNotificationSetting())->createForRegisterUser($newUser->id);
                    $this->adminNotificationUrl = '/customer/active-customer-list';
                    $this->routeCheck = 'cusotmer.list.get-data';
                    $this->typeId = EmailTemplateType::where('type', 'register_email_template')->first()->id; //register email templete typeid
                    $notification = NotificationSetting::where('slug','register')->first();
                    if ($notification) {
                        $this->notificationSend($notification->id, $newUser->id);
                    }
                    $profile = new Profile();
                    $profile->profile_name = $user->name;
                    $newUser->profile()->save($profile);
                    SocialProvider::create([
                        'user_id' => $newUser->id,
                        'provider_id' => $user->getId(),
                        'provider_name' => $provider,
                    ]);
                    Auth::login($newUser, true);
                    $this->dataUpdateWhenLogin($prev_session_id, $buy_it_now);
                    if (Session::has('compare')) {
                        $compare = collect();
                        foreach (Session::get('compare') as $key => $compareItem) {
                            $compareData = Compare::where('product_sku_id', $compareItem['product_sku_id'])->where('customer_id', auth()->user()->id)->first();
                            if ($compareData) {
                            } else {
                                Compare::create([
                                    'product_sku_id' => $compareItem['product_sku_id'],
                                    'data_type' => $compareItem['data_type'],
                                    'product_type' => $compareItem['product_type'],
                                    'customer_id' => auth()->user()->id,
                                ]);
                            }
                        }
                    }
                    return redirect($this->redirectTo());
                }else{
                    Toastr::error(__('Email Already Taken By Normal Registration.'), __('common.error'));
                    return redirect()->route('login');
                }
            }
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.Something Went Wrong'), __('common.error'));
            return redirect()->route('frontend.welcome');
        }
    }
    private function slugify($value)
    {
        $slug = strtolower(str_replace(' ', '-', $value));
        $count = User::where('username', 'LIKE', '%' . $value . '%')->count();
        $suffix = $count ? $count + 1 : "";
        $slug .= $suffix;
        return $slug;
    }
    public function social_login(Request $request)
    {
        if (Auth::attempt([$this->username() => $request->login, 'password' => $request->password])) {
            $user = User::where($this->username(), $request->login)->first();
            $user->social_providers()->create([
                'provider_id' => $request->provider_id,
                'provider_name' => $request->provider_name,
            ]);
            return $this->login($request);
        } else {
            session()->flash('error', 'Wrong Credentials');

            return view(url('/login'));
        }
    }
    public function social_connect(Request $request)
    {
        SocialProvider::create([
            'user_id' => Auth::user()->id,
            'provider_id' => $request->provider_id,
            'provider_name' => $request->provider_name,
        ]);
    }

    public function social_delete($providerId)
    {
        $social = SocialProvider::whereProviderId($providerId)->first();
        $social->delete();

    }
    public function setupSidebar($user)
    {
        $role_id = $user->role->type;
        if ($role_id == 'seller') {
            $backend_menus = Backendmenu::where(function($q){
                $q->where('user_id', auth()->id())->orWhereNull('user_id');
            })->where('is_seller', 1)->get();
        }else{
            $backend_menus = Backendmenu::where(function($q){
                $q->where('user_id', auth()->id())->orWhereNull('user_id');
            })->where('is_admin', 1)->get();

        }
        $backendMenuUser = BackendmenuUser::with('backendMenu')->where('user_id', $user->id)->get();
            if($backendMenuUser->count() != $backend_menus->count()){

                $backend_menu_not_exsist = $backend_menus->whereNotIn('id', $backendMenuUser->pluck('backendmenu_id')->toArray());
                foreach($backend_menu_not_exsist as $menu){

                    $parent_id = null;
                    $position = 0;
                    if($menu->parent_id){
                        $parentMenu = BackendmenuUser::where('backendmenu_id', $menu->parent_id)->where('user_id', $user->id)->first();
                        if($parentMenu){
                            $parent_id  = $parentMenu->id;
                            $position = BackendmenuUser::where('parent_id', $parent_id)->where('user_id', $user->id)->count() + 1;
                        }
                    }

                    BackendmenuUser::create(['parent_id' => $parent_id, 'user_id' => $user->id, 'backendmenu_id' => $menu->id, 'position' => $position]);
                }
            }
    }
}
