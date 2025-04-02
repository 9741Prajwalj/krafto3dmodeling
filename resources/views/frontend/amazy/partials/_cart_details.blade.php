@php
    $all_select_count = 0;
    $subtotal = 0;
    $tax = 0;
    $discount = 0;
    $actual_price = 0;
    $shipping_cost = 0;
    $sellect_seller = 0;
    $selected_product_check  = 0;

    foreach ($cartData as $key => $items) {
        $all_select_count += count($items);
        $sellect_seller  = $key;
        $p = 0;
        foreach ($items as $key => $data) {
            $tax_amount = !empty($data->product) && !empty($data->product->product) && !empty($data->product->product->tax) ? $data->product->product->tax :0;
            $tax += ($tax_amount * $data->qty) ;
            if ($data->is_select == 1) {
                $all_select_count = $all_select_count - 1;
                $selected_product_check ++;
                $p = 1;
            }
        }
        if($p == 1){
            $shipping_cost += 20;

        }
    }
@endphp
<div class="checkout_v3_area">
    <form id="cart_form">
        <div class="checkout_v3_left d-flex justify-content-end mb-0">
            @if(count($cartData) > 0)

                <div class="checkout_v3_inner w-100">

                    @if(!isModuleActive('MultiVendor'))
                        @if($free_shipping)
                            <div class="free_shipping_message">
                                <h5>{{__('shipping.shipping_charge_free_from')}} <span>{{single_price($free_shipping->minimum_shopping)}}</span></h5>
                            </div>
                        @endif

                        <div class="amazy_table4">
                            <div class="amazy_table4_head mb_20 d-none d-lg-flex ">
                                <div class="row d-none d-lg-flex flex-fill">
                                    <div class="col-md-5 fw-600"> <h4 class="font_14 f_w_700 m-0 text-nowrap priamry_text text-uppercase">{{__('common.products')}}</h4> </div>
                                    <div class="col fw-600"> <h4 class="font_14 f_w_700 m-0 text-nowrap priamry_text text-uppercase">{{__('common.price')}}</h4> </div>
                                    <div class="col fw-600"> <h4 class="font_14 f_w_700 m-0 text-nowrap priamry_text text-uppercase">{{__('common.quantity')}}</h4> </div>
                                    <div class="col fw-600"> <h4 class="font_14 f_w_700 m-0 text-nowrap priamry_text text-uppercase">{{__('common.subtotal')}}</h4> </div>
                                    <div class="col-auto fw-600"> <h4 class="font_14 f_w_700 m-0 text-nowrap priamry_text text-uppercase">{{__('common.remove')}}</h4> </div>
                                </div>
                            </div>
                            <ul class="amazy_table4_body">
                                @foreach($cartData as $admin_id => $items)
                                    @foreach($items as $key => $cart)
                                        @if($cart->product_type == 'product')
                                            @if($cart->is_select == 1)
                                                @php
                                                    $pro_price = 0;
                                                    if (isModuleActive('WholeSale')){
                                                        $w_main_price = 0;
                                                        $wholeSalePrices = $cart->product->wholeSalePrices;
                                                        if($wholeSalePrices->count()){
                                                            foreach ($wholeSalePrices as $w_p){
                                                                if ( ($w_p->min_qty<=$cart->qty) && ($w_p->max_qty >=$cart->qty) ){
                                                                    $w_main_price = $w_p->sell_price;
                                                                }
                                                                elseif($w_p->max_qty < $cart->qty){
                                                                    $w_main_price = $w_p->sell_price;
                                                                }
                                                            }
                                                        }

                                                        if ($w_main_price!=0){
                                                            $subtotal += $w_main_price * $cart->qty;
                                                            $pro_price = $w_main_price;
                                                        }else{
                                                            $subtotal += $cart->product->sell_price * $cart->qty;
                                                            $pro_price = $cart->product->sell_price;
                                                        }
                                                    }else{
                                                        $subtotal += $cart->product->sell_price * $cart->qty;
                                                        $pro_price = $cart->product->sell_price;
                                                    }

                                                @endphp
                                            @endif
                                            <li class="list-group-item px-0 px-lg-3 mb_10">
                                                <div class="row gutters-5 align-items-center m-0">
                                                    <div class="col-lg-5 d-flex p-0">
                                                        <a href="{{singleProductURL(@$cart->seller->slug, @$cart->product->product->slug)}}" class="d-flex align-items-center gap_20 cart_thumb_div">
                                                            <div class="thumb">
                                                                <img src="
                                                                @if(@$cart->product->product->product->product_type == 1)
                                                                {{showImage(@$cart->product->product->product->thumbnail_image_source)}}
                                                                @else
                                                                {{showImage(@$cart->product->sku->variant_image?@$cart->product->sku->variant_image:@$cart->product->product->product->thumbnail_image_source)}}
                                                                @endif
                                                                " alt="{{ textLimit(@$cart->product->product->product_name, 30) }}" title="{{ textLimit(@$cart->product->product->product_name, 30) }}">
                                                            </div>
                                                            <div class="summery_pro_content">
                                                                <h4 class="font_16 f_w_700 m-0 theme_hover">{{ textLimit(@$cart->product->product->product_name, 30) }}</h4>
                                                                <p class="font_14 f_w_400 m-0 ">
                                                                    @if(@$cart->product->product->product->product_type == 2)
                                                                        @foreach(@$cart->product->product_variations as $key => $combination)
                                                                            @if(@$combination->attribute->id == 1)
                                                                                {{@$combination->attribute->name}}: {{@$combination->attribute_value->color->name}}
                                                                            @else
                                                                                {{@$combination->attribute->name}}: {{@$combination->attribute_value->value}}
                                                                            @endif
                                                                            @if($key < count(@$cart->product->product_variations)-1),@endif

                                                                        @endforeach
                                                                    @endif
                                                                </p>

                                                                <div class="d-flex flex-wrap align-items-center gap-10 mt-3">
                                                                    <div class="d-block d-lg-none">
                                                                        <div class="product_number_count style_4" data-target="amount-3">
                                                                            <button class="count_single_item inumber_decrement change_qty" data-qty_id="#qty_{{$cart->id}}" data-change_amount="1" data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                                data-minimum_qty="#minimum_qty_{{$cart->id}}" data-product_stock="{{@$cart->product->product_stock}}" data-stock_manage="{{@$cart->product->product->stock_manage}}" data-wholesale="#getWholesalePrice_{{$cart->id}}" data-cart_id="{{$cart->id}}" type="button" value="-"> <i class="ti-minus"></i></button>
                                                                            <input name="qty[]" id="qty_{{$cart->id}}" maxlength="12" data-value="{{$cart->qty}}" value="{{getNumberTranslate($cart->qty)}}" class="count_single_item input-number qty" type="text" data-qty_id="#qty_{{$cart->id}}" data-change_amount="1" data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                            data-minimum_qty="#minimum_qty_{{$cart->id}}" data-product_stock="{{@$cart->product->product_stock}}" data-stock_manage="{{@$cart->product->product->stock_manage}}" data-wholesale="#getWholesalePrice_{{$cart->id}}" data-cart_id="{{$cart->id}}">
                                                                            <input type="hidden" value="{{$cart->id}}" name="cart_id[]">
                                                                            <input type="hidden" id="maximum_qty_{{$cart->id}}" value="{{@$cart->product->product->product->max_order_qty}}">
                                                                            <input type="hidden" id="minimum_qty_{{$cart->id}}" value="{{@$cart->product->product->product->minimum_order_qty}}">
                                                                            <button class="count_single_item number_increment change_qty" data-qty_id="#qty_{{$cart->id}}" data-change_amount="1" data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                                data-minimum_qty="#minimum_qty_{{$cart->id}}" data-product_stock="{{@$cart->product->product_stock}}" data-stock_manage="{{@$cart->product->product->stock_manage}}" data-wholesale="#getWholesalePrice_{{$cart->id}}" data-cart_id="{{$cart->id}}" type="button" value="+"> <i class="ti-plus"></i></button>

                                                                            <!-- for wholesale module -->
                                                                            @if(isModuleActive('WholeSale'))
                                                                                <input type="hidden" id="getWholesalePrice_{{$cart->id}}" value="@if(@$cart->product->wholeSalePrices->count()){{ json_encode(@$cart->product->wholeSalePrices) }} @else 0 @endif">
                                                                            @endif

                                                                        </div>
                                                                    </div>

                                                                    <div class="d-inline">
                                                                        <h4 class="font_16 f_w_700 m-0 lh-1 d-inline d-lg-none text-nowrap">
                                                                            {{single_price($cart->total_price)}}
                                                                        </h4>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                        </a>
                                                    </div>
                                                    <div class="col-lg col-4 order-1 order-lg-0 my-3 my-lg-0 d-none d-lg-block">
                                                        <span class="opacity-60 font_12 d-block d-lg-none">{{__('common.price')}}</span>
                                                        @if($cart->product->product->hasDeal)
                                                            @if($cart->product->product->hasDeal->discount > 0)
                                                                @if($cart->product->product->hasDeal->discount_type == 0)
                                                                    <span class="green_badge text-nowrap">-{{getNumberTranslate($cart->product->product->hasDeal->discount)}}%</span>
                                                                @else
                                                                    <span class="green_badge text-nowrap">-{{single_price($cart->product->product->hasDeal->discount)}}</span>
                                                                @endif
                                                            @endif
                                                        @else
                                                            @if(@$cart->product->product->hasDiscount == 'yes')
                                                                @if($cart->product->product->discount_type == 0)
                                                                    <span class="green_badge text-nowrap">-{{getNumberTranslate($cart->product->product->discount)}}%</span>
                                                                @else
                                                                    <span class="green_badge text-nowrap">-{{single_price($cart->product->product->discount)}}</span>
                                                                @endif
                                                            @endif
                                                        @endif
                                                        <h4 class="font_16 f_w_700 m-0 text-nowrap set_base_price{{$cart->id}}">{{single_price(isset($pro_price)?$pro_price:@$cart->product->sell_price)}}</h4>
                                                        <input type="hidden" class="get_base_price{{$cart->id}}" value="{{single_price(isset($pro_price)?$pro_price:@$cart->product->sell_price)}}">
                                                    </div>
                                                    <div class="col-lg col-6 order-4 order-lg-0 d-none d-lg-block">
                                                        <div class="product_number_count style_4" data-target="amount-3">
                                                            <button class="count_single_item inumber_decrement change_qty" data-qty_id="#qty_{{$cart->id}}" data-change_amount="1" data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                data-minimum_qty="#minimum_qty_{{$cart->id}}" data-product_stock="{{@$cart->product->product_stock}}" data-stock_manage="{{@$cart->product->product->stock_manage}}" data-wholesale="#getWholesalePrice_{{$cart->id}}" data-cart_id="{{$cart->id}}" type="button" value="-"> <i class="ti-minus"></i></button>
                                                            <input name="qty[]" id="qty_{{$cart->id}}" maxlength="12" data-value="{{$cart->qty}}" value="{{getNumberTranslate($cart->qty)}}" class="count_single_item input-number qty" type="text" data-qty_id="#qty_{{$cart->id}}" data-change_amount="1" data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                            data-minimum_qty="#minimum_qty_{{$cart->id}}" data-product_stock="{{@$cart->product->product_stock}}" data-stock_manage="{{@$cart->product->product->stock_manage}}" data-wholesale="#getWholesalePrice_{{$cart->id}}" data-cart_id="{{$cart->id}}">
                                                            <input type="hidden" value="{{$cart->id}}" name="cart_id[]">
                                                            <input type="hidden" id="maximum_qty_{{$cart->id}}" value="{{@$cart->product->product->product->max_order_qty}}">
                                                            <input type="hidden" id="minimum_qty_{{$cart->id}}" value="{{@$cart->product->product->product->minimum_order_qty}}">
                                                            <button class="count_single_item number_increment change_qty" data-qty_id="#qty_{{$cart->id}}" data-change_amount="1" data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                data-minimum_qty="#minimum_qty_{{$cart->id}}" data-product_stock="{{@$cart->product->product_stock}}" data-stock_manage="{{@$cart->product->product->stock_manage}}" data-wholesale="#getWholesalePrice_{{$cart->id}}" data-cart_id="{{$cart->id}}" type="button" value="+"> <i class="ti-plus"></i></button>

                                                            <!-- for wholesale module -->
                                                            @if(isModuleActive('WholeSale'))
                                                                <input type="hidden" id="getWholesalePrice_{{$cart->id}}" value="@if(@$cart->product->wholeSalePrices->count()){{ json_encode(@$cart->product->wholeSalePrices) }} @else 0 @endif">
                                                            @endif

                                                        </div>
                                                    </div>
                                                    <div class="col-lg col-4 order-3 order-lg-0 my-3 my-lg-0 d-none d-lg-block">
                                                        <span class="opacity-60 font_12 d-none d-sm-block d-lg-none">{{__('common.total')}}</span>
                                                        <h4 class="font_16 f_w_700 m-0 lh-1 d-none d-lg-block">
                                                            {{single_price($cart->total_price)}}
                                                        </h4>
                                                    </div>
                                                    <div class="col-lg-auto order-5 order-lg-0 text-end">

                                                        <span class="close_icon style_2 lh-1 cart_item_delete_btn cursor_pointer" data-id="{{$cart->id}}" data-product_id="{{$cart->product_id}}" data-unique_id="#delete_item_{{$cart->id}}" id="delete_item_{{$cart->id}}">
                                                            <svg  width="12.249" height="15.076" viewBox="0 0 12.249 15.076">
                                                                <g  transform="translate(-48)">
                                                                    <path  data-name="Path 1449" d="M59.071,1.884H56.48V1.413A1.415,1.415,0,0,0,55.067,0H53.182a1.415,1.415,0,0,0-1.413,1.413v.471H49.178A1.179,1.179,0,0,0,48,3.062V4.711a.471.471,0,0,0,.471.471h.257l.407,8.547a1.412,1.412,0,0,0,1.412,1.346H57.7a1.412,1.412,0,0,0,1.412-1.346l.407-8.547h.257a.471.471,0,0,0,.471-.471V3.062A1.179,1.179,0,0,0,59.071,1.884Zm-6.36-.471a.472.472,0,0,1,.471-.471h1.884a.472.472,0,0,1,.471.471v.471H52.711ZM48.942,3.062a.236.236,0,0,1,.236-.236h9.893a.236.236,0,0,1,.236.236V4.24H48.942Zm9.23,10.623a.471.471,0,0,1-.471.449H50.547a.471.471,0,0,1-.471-.449l-.4-8.5h8.905Z" fill="#00124e"></path>
                                                                    <path  data-name="Path 1450" d="M240.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,1,0-.942,0V214.6A.471.471,0,0,0,240.471,215.067Z" transform="translate(-186.347 -201.875)" fill="#00124e"></path>
                                                                    <path  data-name="Path 1451" d="M320.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,1,0-.942,0V214.6A.471.471,0,0,0,320.471,215.067Z" transform="translate(-263.991 -201.875)" fill="#00124e"></path>
                                                                    <path  data-name="Path 1452" d="M160.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,0,0-.942,0V214.6A.471.471,0,0,0,160.471,215.067Z" transform="translate(-108.702 -201.875)" fill="#00124e"></path>
                                                                </g>
                                                            </svg>
                                                        </span>
                                                    </div>
                                                </div>
                                            </li>
                                        @else
                                            @if($cart->is_select == 1)
                                                @php
                                                    $subtotal += $cart->giftCard->sell_price * $cart->qty;
                                                @endphp
                                            @endif

                                            <li class="list-group-item px-0 px-lg-3 mb_10">
                                                <div class="row gutters-5 align-items-center">
                                                    <div class="col-lg-5 d-flex">
                                                        <a href="{{route('frontend.gift-card.show',$cart->giftCard->sku)}}" class="d-flex align-items-center gap_20 cart_thumb_div">
                                                            <div class="thumb">
                                                                <img src="{{showImage(@$cart->giftCard->thumbnail_image)}}" alt="{{ textLimit(@$cart->giftCard->name, 30) }}" title="{{ textLimit(@$cart->giftCard->name, 30) }}">
                                                            </div>
                                                            <div class="summery_pro_content">
                                                                <h4 class="font_16 f_w_700 m-0 theme_hover">{{ textLimit(@$cart->giftCard->name, 30) }}</h4>
                                                            </div>
                                                        </a>
                                                    </div>

                                                    <div class="col-lg col-4 order-1 order-lg-0 my-3 my-lg-0">
                                                        <span class="opacity-60 font_12 d-block d-lg-none">{{__('common.price')}}</span>

                                                        <h4 class="font_16 f_w_700 m-0 text-nowrap">{{single_price($cart->price)}}</h4>
                                                    </div>
                                                    <div class="col-lg col-6 order-4 order-lg-0">
                                                        <div class="product_number_count style_4" data-target="amount-3">
                                                            <input type="hidden" value="{{$cart->id}}" name="cart_id[]">
                                                            <input type="hidden" id="maximum_qty_{{$cart->id}}" value="">
                                                            <input type="hidden" id="minimum_qty_{{$cart->id}}" value="1">
                                                            <button class="count_single_item inumber_decrement change_qty" data-qty_id="#qty_{{$cart->id}}" data-cart_id="{{$cart->id}}" data-change_amount="1" data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                data-minimum_qty="#minimum_qty_{{$cart->id}}" data-product_stock="0" data-stock_manage="0" data-wholesale="#getWholesalePrice_{{$cart->id}}" type="button" value="-"> <i class="ti-minus"></i></button>
                                                            <input name="qty[]" id="qty_{{$cart->id}}" data-value="{{$cart->qty}}" value="{{getNumberTranslate($cart->qty)}}" class="count_single_item input-number qty" type="text" data-qty_id="#qty_{{$cart->id}}" data-cart_id="{{$cart->id}}" data-change_amount="1" data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                            data-minimum_qty="#minimum_qty_{{$cart->id}}" data-product_stock="0" data-stock_manage="0" data-wholesale="#getWholesalePrice_{{$cart->id}}">
                                                            <button class="count_single_item number_increment change_qty" data-qty_id="#qty_{{$cart->id}}" data-cart_id="{{$cart->id}}" data-change_amount="1" data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                data-minimum_qty="#minimum_qty_{{$cart->id}}" data-product_stock="0" data-stock_manage="0" data-wholesale="#getWholesalePrice_{{$cart->id}}" type="button" value="+"> <i class="ti-plus"></i></button>
                                                            <!-- for wholesale module -->
                                                            @if(isModuleActive('WholeSale'))
                                                                <input type="hidden" id="getWholesalePrice_{{$cart->id}}" value="0">
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-lg col-4 order-3 order-lg-0 my-3 my-lg-0">
                                                        <span class="opacity-60 font_12 d-block d-lg-none">{{__('common.total')}}</span>
                                                        <h4 class="font_16 f_w_700 m-0 lh-1">
                                                            {{single_price($cart->total_price)}}
                                                        </h4>
                                                    </div>
                                                    <div class="col-lg-auto col-6 order-5 order-lg-0 text-end">
                                                        <span class="close_icon style_2 lh-1 cart_item_delete_btn cursor_pointer" data-id="{{$cart->id}}" data-product_id="{{$cart->product_id}}" data-unique_id="#delete_item_{{$cart->id}}" id="delete_item_{{$cart->id}}">
                                                            <svg  width="12.249" height="15.076" viewBox="0 0 12.249 15.076">
                                                                <g  transform="translate(-48)">
                                                                    <path  data-name="Path 1449" d="M59.071,1.884H56.48V1.413A1.415,1.415,0,0,0,55.067,0H53.182a1.415,1.415,0,0,0-1.413,1.413v.471H49.178A1.179,1.179,0,0,0,48,3.062V4.711a.471.471,0,0,0,.471.471h.257l.407,8.547a1.412,1.412,0,0,0,1.412,1.346H57.7a1.412,1.412,0,0,0,1.412-1.346l.407-8.547h.257a.471.471,0,0,0,.471-.471V3.062A1.179,1.179,0,0,0,59.071,1.884Zm-6.36-.471a.472.472,0,0,1,.471-.471h1.884a.472.472,0,0,1,.471.471v.471H52.711ZM48.942,3.062a.236.236,0,0,1,.236-.236h9.893a.236.236,0,0,1,.236.236V4.24H48.942Zm9.23,10.623a.471.471,0,0,1-.471.449H50.547a.471.471,0,0,1-.471-.449l-.4-8.5h8.905Z" fill="#00124e"></path>
                                                                    <path  data-name="Path 1450" d="M240.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,1,0-.942,0V214.6A.471.471,0,0,0,240.471,215.067Z" transform="translate(-186.347 -201.875)" fill="#00124e"></path>
                                                                    <path  data-name="Path 1451" d="M320.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,1,0-.942,0V214.6A.471.471,0,0,0,320.471,215.067Z" transform="translate(-263.991 -201.875)" fill="#00124e"></path>
                                                                    <path  data-name="Path 1452" d="M160.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,0,0-.942,0V214.6A.471.471,0,0,0,160.471,215.067Z" transform="translate(-108.702 -201.875)" fill="#00124e"></path>
                                                                </g>
                                                            </svg>
                                                        </span>
                                                    </div>
                                                </div>
                                            </li>
                                        @endif
                                        @if($cart->is_select == 1)
                                            @php
                                                $actual_price += $cart->total_price;
                                            @endphp
                                        @endif
                                    @endforeach
                                @endforeach

                            </ul>
                        </div>
                    @else
                        <div class="amazy_table4">
                            <div class="amazy_table4_head mb_20 d-none d-lg-flex ">
                                <div class="row d-none d-lg-flex flex-fill">
                                    <div class="col-md-5 fw-600"> <h4 class="font_14 f_w_700 m-0 text-nowrap priamry_text text-uppercase">{{__('common.products')}}</h4> </div>
                                    <div class="col fw-600"> <h4 class="font_14 f_w_700 m-0 text-nowrap priamry_text text-uppercase">{{__('common.price')}}</h4> </div>
                                    <div class="col fw-600"> <h4 class="font_14 f_w_700 m-0 text-nowrap priamry_text text-uppercase">{{__('common.quantity')}}</h4> </div>
                                    <div class="col fw-600"> <h4 class="font_14 f_w_700 m-0 text-nowrap priamry_text text-uppercase">{{__('common.subtotal')}}</h4> </div>
                                    <div class="col-auto fw-600"> <h4 class="font_14 f_w_700 m-0 text-nowrap priamry_text text-uppercase">{{__('common.remove')}}</h4> </div>
                                </div>
                            </div>
                            @foreach($cartData as $seller_id => $cartItems)
                                @php
                                    $seller = App\Models\User::where('id',$seller_id)->first();
                                    $select_count = count($cartItems);
                                @endphp
                                @foreach($cartItems as $m => $data)
                                    @php
                                        if($data->is_select == 1){
                                                $select_count = $select_count - 1;
                                        }else{
                                            $select_count = $select_count;
                                        }
                                    @endphp
                                @endforeach
                                <div class="checkout_shiped_box mb_20">
                                    <ul class="amazy_table4_body">
                                        @foreach($cartItems as $key => $cart)
                                            @if($cart->product_type == 'product')
                                                @if($cart->is_select == 1)
                                                    @php
                                                        $pro_price = 0;
                                                        if (isModuleActive('WholeSale')){
                                                            $w_main_price = 0;
                                                            $wholeSalePrices = @$cart->product->wholeSalePrices;
                                                            if($wholeSalePrices->count()){
                                                                foreach ($wholeSalePrices as $w_p){
                                                                    if ( ($w_p->min_qty<=$cart->qty) && ($w_p->max_qty >=$cart->qty) ){
                                                                        $w_main_price = $w_p->sell_price;
                                                                    }
                                                                    elseif($w_p->max_qty < $cart->qty){
                                                                        $w_main_price = $w_p->sell_price;
                                                                    }
                                                                }
                                                            }
                                                            if ($w_main_price!=0){
                                                                $subtotal += $w_main_price * $cart->qty;
                                                                $pro_price = $w_main_price;
                                                            }else{
                                                                $subtotal += $cart->product->sell_price * $cart->qty;
                                                                $pro_price = $cart->product->sell_price;
                                                            }
                                                        }else{
                                                            $subtotal += $cart->product->sell_price * $cart->qty;
                                                            $pro_price = $cart->product->sell_price;
                                                        }
                                                    @endphp
                                                @endif

                                                <li class="list-group-item px-0 px-lg-3 mb_10">
                                                    <div class="row gutters-5 m-0 align-items-center">
                                                        <div class="col-lg-5 d-flex p-0">
                                                            <a href="{{singleProductURL(@$cart->seller->slug, @$cart->product->product->slug)}}" class="d-flex align-items-center gap_20 cart_thumb_div">
                                                                <div class="thumb">
                                                                    <img src="
                                                                        @if(@$cart->product->product->product->product_type == 1)
                                                                            {{showImage(@$cart->product->product->product->thumbnail_image_source)}}
                                                                        @else
                                                                            {{showImage(@$cart->product->sku->variant_image?@$cart->product->sku->variant_image:@$cart->product->product->product->thumbnail_image_source)}}
                                                                        @endif
                                                                    " alt="{{ textLimit(@$cart->product->product->product_name, 35) }}" title="{{ textLimit(@$cart->product->product->product_name, 35) }}">
                                                                </div>
                                                                <div class="summery_pro_content">
                                                                    <h4 class="font_16 f_w_700 m-0 theme_hover">{{ textLimit(@$cart->product->product->product_name, 35) }}</h4>
                                                                    <p class="font_14 f_w_400 m-0 ">
                                                                        @if(@$cart->product->product->product->product_type == 2)
                                                                            @foreach(@$cart->product->product_variations as $key => $combination)
                                                                                @if(@$combination->attribute->id == 1)
                                                                                    {{@$combination->attribute->name}}: {{@$combination->attribute_value->color->name}}
                                                                                @else
                                                                                    {{@$combination->attribute->name}}: {{@$combination->attribute_value->value}}
                                                                                @endif
                                                                                @if($key < count(@$cart->product->product_variations)-1),@endif
                                                                            @endforeach
                                                                        @endif
                                                                    </p>


                                                                    <div class="d-flex flex-wrap align-items-center gap-10 mt-3">
                                                                        <div class="d-block d-lg-none">
                                                                            <div class="product_number_count style_4" data-target="amount-3">
                                                                                <button class="count_single_item inumber_decrement change_qty" data-qty_id="#qty_{{$cart->id}}" data-change_amount="1" data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                                    data-minimum_qty="#minimum_qty_{{$cart->id}}" data-product_stock="{{$cart->product->product_stock}}" data-stock_manage="{{$cart->product->product->stock_manage}}" data-wholesale="#getWholesalePrice_{{$cart->id}}" data-cart_id="{{$cart->id}}" type="button" value="-"> <i class="ti-minus"></i></button>
                                                                                <input name="qty[]" id="qty_{{$cart->id}}" maxlength="12" data-value="{{$cart->qty}}" value="{{getNumberTranslate($cart->qty)}}" class="count_single_item input-number qty" type="text" data-qty_id="#qty_{{$cart->id}}" data-change_amount="1" data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                                data-minimum_qty="#minimum_qty_{{$cart->id}}" data-product_stock="{{$cart->product->product_stock}}" data-stock_manage="{{$cart->product->product->stock_manage}}" data-wholesale="#getWholesalePrice_{{$cart->id}}" data-cart_id="{{$cart->id}}">
                                                                                <input type="hidden" value="{{$cart->id}}" name="cart_id[]">
                                                                                <input type="hidden" id="maximum_qty_{{$cart->id}}" value="{{$cart->product->product->product->max_order_qty}}">
                                                                                <input type="hidden" id="minimum_qty_{{$cart->id}}" value="{{$cart->product->product->product->minimum_order_qty}}">
                                                                                <button class="count_single_item number_increment change_qty" data-qty_id="#qty_{{$cart->id}}" data-change_amount="1" data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                                    data-minimum_qty="#minimum_qty_{{$cart->id}}" data-product_stock="{{$cart->product->product_stock}}" data-stock_manage="{{$cart->product->product->stock_manage}}" data-wholesale="#getWholesalePrice_{{$cart->id}}" data-cart_id="{{$cart->id}}" type="button" value="+"> <i class="ti-plus"></i></button>

                                                                                    <!-- for wholesale module -->
                                                                                    @if(isModuleActive('WholeSale'))
                                                                                        <input type="hidden" id="getWholesalePrice_{{$cart->id}}" value="@if(@$cart->product->wholeSalePrices->count()){{ json_encode(@$cart->product->wholeSalePrices) }} @else 0 @endif">
                                                                                    @endif
                                                                            </div>
                                                                        </div>

                                                                        <div class="d-inline">
                                                                            <h4 class="font_16 f_w_700 m-0 lh-1 d-inline d-lg-none text-nowrap">
                                                                                {{single_price($cart->total_price)}}
                                                                            </h4>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </div>

                                                        <div class="col-lg col-4 order-1 order-lg-0 my-3 my-lg-0 d-none d-lg-block">
                                                            <span class="opacity-60 font_12 d-block d-lg-none">{{__('common.price')}}</span>
                                                            @if($cart->product->product->hasDeal)
                                                                @if($cart->product->product->hasDeal->discount > 0)
                                                                    @if($cart->product->product->hasDeal->discount_type == 0)
                                                                        <span class="green_badge text-nowrap">-{{getNumberTranslate($cart->product->product->hasDeal->discount)}}%</span>
                                                                    @else
                                                                        <span class="green_badge text-nowrap">-{{single_price($cart->product->product->hasDeal->discount)}}</span>
                                                                    @endif
                                                                @endif
                                                            @else
                                                                @if(@$cart->product->product->hasDiscount == 'yes')
                                                                    @if($cart->product->product->discount_type == 0)
                                                                        <span class="green_badge text-nowrap">-{{getNumberTranslate($cart->product->product->discount)}}%</span>
                                                                    @else
                                                                        <span class="green_badge text-nowrap">-{{single_price($cart->product->product->discount)}}</span>
                                                                    @endif
                                                                @endif
                                                            @endif
                                                            <h4 class="font_16 f_w_700 m-0 set_base_price{{$cart->id}}">{{single_price(isset($pro_price)?$pro_price:@$cart->product->sell_price)}}</h4>
                                                            <input type="hidden" class="get_base_price{{$cart->id}}" value="{{single_price(isset($pro_price)?$pro_price:@$cart->product->sell_price)}}">
                                                        </div>
                                                        <div class="col-lg col-6 order-4 order-lg-0 d-none d-lg-block">
                                                            <div class="product_number_count style_4" data-target="amount-3">
                                                                <button class="count_single_item inumber_decrement change_qty" data-qty_id="#qty_{{$cart->id}}" data-change_amount="1" data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                    data-minimum_qty="#minimum_qty_{{$cart->id}}" data-product_stock="{{$cart->product->product_stock}}" data-stock_manage="{{$cart->product->product->stock_manage}}" data-wholesale="#getWholesalePrice_{{$cart->id}}" data-cart_id="{{$cart->id}}" type="button" value="-"> <i class="ti-minus"></i></button>
                                                                <input name="qty[]" id="qty_{{$cart->id}}" maxlength="12" data-value="{{$cart->qty}}" value="{{getNumberTranslate($cart->qty)}}" class="count_single_item input-number qty" type="text" data-qty_id="#qty_{{$cart->id}}" data-change_amount="1" data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                data-minimum_qty="#minimum_qty_{{$cart->id}}" data-product_stock="{{$cart->product->product_stock}}" data-stock_manage="{{$cart->product->product->stock_manage}}" data-wholesale="#getWholesalePrice_{{$cart->id}}" data-cart_id="{{$cart->id}}">
                                                                <input type="hidden" value="{{$cart->id}}" name="cart_id[]">
                                                                <input type="hidden" id="maximum_qty_{{$cart->id}}" value="{{$cart->product->product->product->max_order_qty}}">
                                                                <input type="hidden" id="minimum_qty_{{$cart->id}}" value="{{$cart->product->product->product->minimum_order_qty}}">
                                                                <button class="count_single_item number_increment change_qty" data-qty_id="#qty_{{$cart->id}}" data-change_amount="1" data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                    data-minimum_qty="#minimum_qty_{{$cart->id}}" data-product_stock="{{$cart->product->product_stock}}" data-stock_manage="{{$cart->product->product->stock_manage}}" data-wholesale="#getWholesalePrice_{{$cart->id}}" data-cart_id="{{$cart->id}}" type="button" value="+"> <i class="ti-plus"></i></button>

                                                                    <!-- for wholesale module -->
                                                                    @if(isModuleActive('WholeSale'))
                                                                        <input type="hidden" id="getWholesalePrice_{{$cart->id}}" value="@if(@$cart->product->wholeSalePrices->count()){{ json_encode(@$cart->product->wholeSalePrices) }} @else 0 @endif">
                                                                    @endif
                                                            </div>
                                                        </div>
                                                        <div class="col-lg col-4 order-3 order-lg-0 my-3 my-lg-0 d-none d-lg-block">
                                                            <span class="opacity-60 font_12 d-none d-sm-block d-lg-none">{{__('common.total')}}</span>
                                                            <h4 class="font_16 f_w_700 m-0 lh-1 text-nowrap d-none d-lg-block">
                                                                {{single_price($cart->total_price)}}
                                                            </h4>
                                                        </div>
                                                        <div class="col-lg-auto order-5 order-lg-0 text-end">
                                                            <span class="close_icon style_2 lh-1 cart_item_delete_btn cursor_pointer" data-id="{{$cart->id}}" data-product_id="{{$cart->product_id}}" data-unique_id="#delete_item_{{$cart->id}}" id="delete_item_{{$cart->id}}">
                                                                <svg  width="12.249" height="15.076" viewBox="0 0 12.249 15.076">
                                                                    <g  transform="translate(-48)">
                                                                        <path  data-name="Path 1449" d="M59.071,1.884H56.48V1.413A1.415,1.415,0,0,0,55.067,0H53.182a1.415,1.415,0,0,0-1.413,1.413v.471H49.178A1.179,1.179,0,0,0,48,3.062V4.711a.471.471,0,0,0,.471.471h.257l.407,8.547a1.412,1.412,0,0,0,1.412,1.346H57.7a1.412,1.412,0,0,0,1.412-1.346l.407-8.547h.257a.471.471,0,0,0,.471-.471V3.062A1.179,1.179,0,0,0,59.071,1.884Zm-6.36-.471a.472.472,0,0,1,.471-.471h1.884a.472.472,0,0,1,.471.471v.471H52.711ZM48.942,3.062a.236.236,0,0,1,.236-.236h9.893a.236.236,0,0,1,.236.236V4.24H48.942Zm9.23,10.623a.471.471,0,0,1-.471.449H50.547a.471.471,0,0,1-.471-.449l-.4-8.5h8.905Z" fill="#00124e"></path>
                                                                        <path  data-name="Path 1450" d="M240.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,1,0-.942,0V214.6A.471.471,0,0,0,240.471,215.067Z" transform="translate(-186.347 -201.875)" fill="#00124e"></path>
                                                                        <path  data-name="Path 1451" d="M320.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,1,0-.942,0V214.6A.471.471,0,0,0,320.471,215.067Z" transform="translate(-263.991 -201.875)" fill="#00124e"></path>
                                                                        <path  data-name="Path 1452" d="M160.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,0,0-.942,0V214.6A.471.471,0,0,0,160.471,215.067Z" transform="translate(-108.702 -201.875)" fill="#00124e"></path>
                                                                    </g>
                                                                </svg>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </li>
                                            @else
                                                @if($cart->is_select == 1)
                                                    @if(is_null($cart->giftCard->type))
                                                        @php
                                                            $subtotal += $cart->giftCard->sell_price * $cart->qty;
                                                        @endphp
                                                    @else
                                                        @php
                                                            $subtotal += $cart->giftCard->addGiftCardInfo->gift_selling_price * $cart->qty;
                                                        @endphp
                                                    @endif
                                                @endif

                                                <li class="list-group-item px-0 px-lg-3 mb_10">
                                                    <div class="row gutters-5 align-items-center">
                                                        <div class="col-lg-5 d-flex">
                                                            @if(is_null($cart->giftCard->type))
                                                                <a href="{{route('frontend.gift-card.show',$cart->giftCard->sku)}}" class="d-flex align-items-center gap_20 cart_thumb_div">
                                                                    <div class="thumb">
                                                                        <img src="{{showImage(@$cart->giftCard->thumbnail_image)}}" alt="{{ textLimit(@$cart->giftCard->name, 35) }}" title="{{ textLimit(@$cart->giftCard->name, 35) }}">
                                                                    </div>
                                                                    <div class="summery_pro_content">
                                                                        <h4 class="font_16 f_w_700 m-0 theme_hover">{{ textLimit(@$cart->giftCard->name, 35) }}</h4>
                                                                    </div>
                                                                </a>
                                                            @else
                                                                <a href="{{route('frontend.gift-card.show.multiple',$cart->giftCard->sku)}}" class="d-flex align-items-center gap_20 cart_thumb_div">
                                                                    <div class="thumb">
                                                                        <img src="{{showImage(@$cart->giftCard->thumbnail_image)}}" alt="{{ textLimit(@$cart->giftCard->name, 35) }}" title="{{ textLimit(@$cart->giftCard->name, 35) }}">
                                                                    </div>
                                                                    <div class="summery_pro_content">
                                                                        <h4 class="font_16 f_w_700 m-0 theme_hover">{{ textLimit(@$cart->giftCard->name, 35) }}</h4>
                                                                    </div>
                                                                </a>
                                                            @endif
                                                        </div>

                                                        <div class="col-lg col-4 order-1 order-lg-0 my-3 my-lg-0">
                                                            <span class="opacity-60 font_12 d-block d-lg-none">{{__('common.price')}}</span>
                                                            <h4 class="font_16 f_w_700 m-0 text-nowrap">{{single_price($cart->price)}}</h4>
                                                        </div>
                                                        <div class="col-lg col-6 order-4 order-lg-0">
                                                            <div class="product_number_count style_4" data-target="amount-1">
                                                                <input type="hidden" value="{{$cart->id}}" name="cart_id[]">
                                                                <input type="hidden" id="maximum_qty_{{$cart->id}}" value="">
                                                                <input type="hidden" id="minimum_qty_{{$cart->id}}" value="1">
                                                                <button class="count_single_item inumber_decrement change_qty" data-qty_id="#qty_{{$cart->id}}" data-cart_id="{{$cart->id}}" data-change_amount="1" data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                    data-minimum_qty="#minimum_qty_{{$cart->id}}" data-product_stock="0" data-stock_manage="0" data-wholesale="#getWholesalePrice_{{$cart->id}}" type="button" value="-"> <i class="ti-minus"></i></button>
                                                                <input name="qty[]" id="qty_{{$cart->id}}" data-value="{{$cart->qty}}" value="{{getNumberTranslate($cart->qty)}}" class="count_single_item input-number qty" type="text" data-qty_id="#qty_{{$cart->id}}" data-cart_id="{{$cart->id}}" data-change_amount="1" data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                data-minimum_qty="#minimum_qty_{{$cart->id}}" data-product_stock="0" data-stock_manage="0" data-wholesale="#getWholesalePrice_{{$cart->id}}">
                                                                <button class="count_single_item number_increment change_qty" data-qty_id="#qty_{{$cart->id}}" data-cart_id="{{$cart->id}}" data-change_amount="1" data-maximum_qty="#maximum_qty_{{$cart->id}}"
                                                                    data-minimum_qty="#minimum_qty_{{$cart->id}}" data-product_stock="0" data-stock_manage="0" data-wholesale="#getWholesalePrice_{{$cart->id}}" type="button" value="+"> <i class="ti-plus"></i></button>

                                                                <!-- for wholesale module -->
                                                                @if(isModuleActive('WholeSale'))
                                                                    <input type="hidden" id="getWholesalePrice_{{$cart->id}}" value="0">
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="col-lg col-4 order-3 order-lg-0 my-3 my-lg-0">
                                                            <span class="opacity-60 font_12 d-block d-lg-none">{{__('common.total')}}</span>
                                                            <h4 class="font_16 f_w_700 m-0 lh-1 text-nowrap">
                                                                {{single_price($cart->total_price)}}
                                                            </h4>
                                                        </div>
                                                        <div class="col-lg-auto col-6 order-5 order-lg-0 text-end">
                                                            <span class="close_icon style_2 lh-1 cart_item_delete_btn cursor_pointer" data-id="{{$cart->id}}" data-product_id="{{$cart->product_id}}" data-unique_id="#delete_item_{{$cart->id}}" id="delete_item_{{$cart->id}}">
                                                                <svg  width="12.249" height="15.076" viewBox="0 0 12.249 15.076">
                                                                    <g  transform="translate(-48)">
                                                                        <path  data-name="Path 1449" d="M59.071,1.884H56.48V1.413A1.415,1.415,0,0,0,55.067,0H53.182a1.415,1.415,0,0,0-1.413,1.413v.471H49.178A1.179,1.179,0,0,0,48,3.062V4.711a.471.471,0,0,0,.471.471h.257l.407,8.547a1.412,1.412,0,0,0,1.412,1.346H57.7a1.412,1.412,0,0,0,1.412-1.346l.407-8.547h.257a.471.471,0,0,0,.471-.471V3.062A1.179,1.179,0,0,0,59.071,1.884Zm-6.36-.471a.472.472,0,0,1,.471-.471h1.884a.472.472,0,0,1,.471.471v.471H52.711ZM48.942,3.062a.236.236,0,0,1,.236-.236h9.893a.236.236,0,0,1,.236.236V4.24H48.942Zm9.23,10.623a.471.471,0,0,1-.471.449H50.547a.471.471,0,0,1-.471-.449l-.4-8.5h8.905Z" fill="#00124e"></path>
                                                                        <path  data-name="Path 1450" d="M240.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,1,0-.942,0V214.6A.471.471,0,0,0,240.471,215.067Z" transform="translate(-186.347 -201.875)" fill="#00124e"></path>
                                                                        <path  data-name="Path 1451" d="M320.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,1,0-.942,0V214.6A.471.471,0,0,0,320.471,215.067Z" transform="translate(-263.991 -201.875)" fill="#00124e"></path>
                                                                        <path  data-name="Path 1452" d="M160.471,215.067a.471.471,0,0,0,.471-.471v-6.125a.471.471,0,0,0-.942,0V214.6A.471.471,0,0,0,160.471,215.067Z" transform="translate(-108.702 -201.875)" fill="#00124e"></path>
                                                                    </g>
                                                                </svg>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </li>
                                            @endif
                                            @if($cart->is_select == 1)
                                                @php
                                                    $actual_price += $cart->total_price;
                                                @endphp
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                        @endif
                        <div class="d-flex gap_10 align-items-center flex-wrap mt_20">
                            <div class="d-none d-lg-flex align-items-center gap_10 flex-fill flex-wrap">
                                <a href="{{url('/')}}" class="amaz_primary_btn2 style3">{{__('defaultTheme.continue_shopping')}}</a>
                                <button class="amaz_primary_btn2 style3" data-bs-toggle="modal" data-bs-target="#view3DModal">
                                    {{__('view details')}}
                                </button>
                            </div>
                        <a class="amaz_primary_btn min_200 style2 cursor_pointer @if(count($cartData) > 0) process_to_checkout_check @endif" 
                        data-value="{{$selected_product_check}}">{{__('defaultTheme.proceed_to_checkout')}}</a>
                    </div> 
                </div>
                <!-- for wholesale module -->
                <input type="hidden" id="isWholeSaleActive"     value="{{isModuleActive('WholeSale')}}">
                <!-- for wholesale module -->
                @endif
            </div>
        @if(count($cartData) < 1)
            <div class="col-lg-12 text-center mb_50">
                <span class="product_not_found">{{ __('defaultTheme.no_product_found') }}</span>
            </div>
        @endif
    </form>
    <div class="checkout_v3_right d-flex justify-content-start checkout_summery_div">
        @php
            $grand_total = $actual_price;
            $discount = $subtotal -$actual_price;
        @endphp
        <div class="order_sumery_box flex-fill">
            <h3 class="check_v3_title mb_25">{{__('common.order_summary')}}</h3>
            <div class="subtotal_lists">
                <div class="single_total_list d-flex align-items-center">
                    <div class="single_total_left flex-fill">
                        <h4>{{ __('common.subtotal') }}</h4>
                    </div>
                    <div class="single_total_right">
                        @if (app('general_setting')->price_with_vat)
                        <span>+ {{single_price($subtotal - $tax)}}</span>
                        @else
                            <span>+ {{single_price($subtotal)}}</span>
                        @endif
                    </div>
                </div>
                <div class="single_total_list d-flex align-items-center flex-wrap">
                    <div class="single_total_left flex-fill">
                        <h4>{{__('common.shipping_charge')}}</h4>
                    </div>
                    <div class="single_total_right">
                        <span>{{__('defaultTheme.calculated_at_next_step')}}</span>
                    </div>
                </div>
                <div class="single_total_list d-flex align-items-center flex-wrap">
                    <div class="single_total_left flex-fill">
                        <h4>{{__('common.discount')}}</h4>
                    </div>
                    <div class="single_total_right">
                        <span>- {{single_price($discount)}}</span>
                    </div>
                </div>
                <div class="single_total_list d-flex align-items-center flex-wrap">
                    <div class="single_total_left flex-fill">
                        <h4>{{__('common.vat/tax/gst')}}</h4>
                    </div>
                    <div class="single_total_right">
                        @if (app('general_setting')->price_with_vat)
                            <span>+ {{single_price($tax)}}</span>
                        @else
                            <span>{{__('defaultTheme.calculated_at_next_step')}}</span>
                        @endif
                    </div>
                </div>
                <div class="total_amount d-flex align-items-center flex-wrap">
                    <div class="single_total_left flex-fill">
                        <span class="total_text">{{__('common.total')}}</span>
                    </div>
                    <div class="single_total_right">
                        <span class="total_text" id="grand_total" data-amount='{{ str_replace(',','',number_format($grand_total,2)) }}'><span>{{single_price($grand_total)}}</span></span>
                    </div>
                </div>

                <div class="total_amount d-flex align-items-center flex-wrap mt-2 mb-2">
                   <div id="TabbyPromo"></div>
                </div>
            </div>
        </div>
    </div>
</div>
