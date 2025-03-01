@php
    $footer_content = \Modules\FooterSetting\Entities\FooterContent::first();
    $subscribeContent = \Modules\FrontendCMS\Entities\SubscribeContent::find(1);
    $about_section = Modules\FrontendCMS\Entities\HomePageSection::where('section_name','about_section')->first();
@endphp
@if(url()->current() == url('/'))
<div id="about_section" class="amaz_section section_spacing4 {{ ($about_section)? ($about_section->status == 0?'d-none':'') : ''}}">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section__title d-flex align-items-center gap-3 mb_20">
                    <h3 class="m-0 flex-fill">{{ app('general_setting')->footer_about_title }}</h3>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="amaz_mazing_text">
                    @php echo app('general_setting')->footer_about_description; @endphp
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- FOOTER::START  -->
    <footer class="home_three_footer">
        <div class="main_footer_wrap">
            <div class="container">
                 <div class="row">
                    <div class="col-xl-3 col-lg-3 col-md-6 footer_links_50 ">
                        <div class="footer_widget" >
                            <ul class="footer_links">
                                @foreach($sectionWidgets->where('section','1') as $page)
                                    @if($page->pageData)
                                    @if(!isModuleActive('Lead') && $page->pageData->module == 'Lead')
                                        @continue
                                    @endif
                                    <li><a href="{{ url($page->pageData->slug) }}">{{$page->name}}</a></li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-6 footer_links_50 ">
                        <div class="footer_widget">
                            <ul class="footer_links">
                                @foreach($sectionWidgets->where('section','2') as $page)
                                    @if($page->pageData)
                                        @if(!isModuleActive('Lead') && $page->pageData->module == 'Lead')
                                            @continue
                                        @endif
                                        <li><a href="{{ url($page->pageData->slug) }}">{{$page->name}}</a></li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <!-- *p* -->
                    <div class="col-xl-3 col-lg-3 col-md-6 footer_links_50 ">
                        <div class="footer_widget">
                            <ul class="footer_links">
                                @foreach($sectionWidgets->where('section','3') as $page)
                                    @if($page->pageData)
                                        @if(!isModuleActive('Lead') && $page->pageData->module == 'Lead')
                                            @continue
                                        @endif
                                        <li><a href="{{ url($page->pageData->slug) }}">{{$page->name}}</a></li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <!-- *pe* -->
                    <div class="col-lg-3 col-xl-3 col-md-6">
                        <div class="footer_widget" >

                            <div class="apps_boxs">
                                @if($footer_content->show_play_store)
                                <a href="{{$footer_content->play_store}}" class="google_play_box d-flex align-items-center mb_10">
                                    <div class="icon">
                                        <img src="{{url('/')}}/frontend/amazy/img/amaz_icon/google_play.svg" alt="{{__('amazy.Google Play')}}" title="{{__('amazy.Google Play')}}">
                                    </div>
                                    <div class="google_play_text">
                                        <span>{{__('amazy.Get it on')}}</span>
                                        <h4 class="text-nowrap">{{__('amazy.Google Play')}}</h4>
                                    </div>
                                </a>
                                @endif
                                @if($footer_content->show_app_store)
                                <a href="{{$footer_content->app_store}}" class="google_play_box d-flex align-items-center">
                                    <div class="icon">
                                        <img src="{{url('/')}}/frontend/amazy/img/amaz_icon/apple_icon.svg" alt="{{__('amazy.Apple Store')}}"  title="{{__('amazy.Apple Store')}}">
                                    </div>
                                    <div class="google_play_text">
                                        <span>{{__('amazy.Get it on')}}</span>
                                        <h4 class="text-nowrap">{{__('amazy.Apple Store')}}</h4>
                                    </div>
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    <x-subscribe-component :subscribeContent="$subscribeContent"/>
                </div>
            </div>
        </div>
        <div class="copyright_area p-0 text-center">
            <div class="container">
                <div class="footer_border m-0"></div>
                <div class="row justify-content-center">
                    <div class="col-md-12">
                        <div class="copy_right_text d-flex align-items-center gap_20 flex-wrap justify-content-center">
                            @php echo app('general_setting')->footer_copy_right; @endphp
                        </div>
                    </div>
                </div>
                @if($footer_content->show_payment_image != 0 && $footer_content->payment_image)
                    <div class="footer_border m-0"></div>
                    <div class="row justify-content-center">
                        <div class="col-12">
                            <div class="payment_imgs text-center">
                                <img class="img-fluid" src="{{showImage($footer_content->payment_image)}}" alt="{{__('common.payment_method')}}" title="{{__('common.payment_method')}}">
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </footer>
    <!-- FOOTER::END  -->
@include('frontend.amazy.auth.partials._login_modal')
<div id="cart_data_show_div">
    @include('frontend.amazy.partials._cart_details_submenu')
</div>
<div id="cart_success_modal_div">
    @include('frontend.amazy.partials._cart_success_modal')
</div>
<input type="hidden" id="login_check" value="@if(auth()->check()) 1 @else 0 @endif">
<div class="add-product-to-cart-using-modal">

</div>

@include('frontend.amazy.partials._modals')

<div id="back-top" style="display: none;">
    <a title="{{__('common.go_to_top')}}" href="#"><i class="fas fa-chevron-up"></i></a>
</div>

@php
    $messanger_data = \Modules\GeneralSetting\Entities\FacebookMessage::first();
@endphp
@if($messanger_data->status == 1)
    @php echo $messanger_data->code; @endphp
@endif


@include('frontend.amazy.partials._script')
@stack('scripts')
@stack('wallet_scripts')



</body>

</html>
