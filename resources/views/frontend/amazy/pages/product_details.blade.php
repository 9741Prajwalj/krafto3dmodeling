@extends('frontend.amazy.layouts.app')
@section('title')
    @if(@$product->product->meta_title != null)
        {{ @substr(@$product->product->meta_title,0, 60)}}
    @else
        {{ @substr(@$product->product_name,0, 60)}}
    @endif
@endsection
@section('share_meta')
    @if(@$product->product->meta_description != null)
        <meta property="og:description" content="{{@$product->product->meta_description}}" />
        <meta name="description" content="{{@$product->product->meta_description}}">
    @else
        <meta property="og:description" content="{{ Str::limit(strip_tags($product->product->description),128)}}" />
        <meta name="description" content="{{ Str::limit(strip_tags($product->product->description),128) }}">
    @endif
    @if(@$product->product->meta_title != null)
        <meta name="title" content="{{ @substr(@$product->product->meta_title,0,60) }}"/>
        <meta property="og:title" content="{{substr(@$product->product->meta_title,0,60)}}" />
    @else
        <meta property="og:title" content="{{@substr(@$product->product_name,0,60)}}" />
        <meta name="title" content="{{ @substr(@$product->product_name,0,60) }}"/>
    @endif
    @if(@$product->product->meta_image != null && @getimagesize(showImage(@$product->product->meta_image))[0] > 200)
        <meta property="og:image" content="{{showImage($product->product->meta_image)}}" />
    @elseif(@$product->product->thumbnail_image_source != null && @getimagesize(showImage(@$product->product->thumbnail_image_source))[0] > 200)
        <meta property="og:image" content="{{showImage(@$product->product->thumbnail_image_source)}}" />
    @elseif(count(@$product->product->gallary_images) > 0 && @getimagesize(showImage(@$product->product->gallary_images[0]->images_source))[0] > 200)
        <meta property="og:image" content="{{showImage(@$product->product->gallary_images[0]->images_source)}}" />
    @endif


    <meta property="og:url" content="{{singleProductURL(@$product->seller->slug, $product->slug)}}" />
    <meta property="og:image:width" content="400" />
    <meta property="og:image:height" content="300" />
    <meta property="og:type" content="og:product">
    @php
        $total_tag = count($product->product->tags);
        $meta_tags = '';
        foreach($product->product->tags as $key => $tag){
            if($key + 1 < $total_tag){
                $meta_tags .= $tag->name.', ';
            }else{
                $meta_tags .= $tag->name;
            }
        }
        if(!empty($product->product->meta_title)){
            $product_title = $product->product->meta_title;
        }else{
            $product_title = $product->product_name;
        }

    @endphp
    @php
        $public_code = null;
        $payment = DB::table('payment_methods')->where('method','Tabby')->where('active_status',1)->first();
        if($payment)
        {
            $tabby_gateway = getPaymentGatewayInfo($payment->id);
            if($tabby_gateway){
                $public_code = $tabby_gateway->perameter_1;
            }
        }

    @endphp
     <meta property="product:plural_title"      content="{{ $product_title }}" />
     <meta property="product:price:amount"      content="{{str_replace('$','',getProductDiscountedPrice($product))}}"/>
     <meta property="product:price:currency"    content="{{ currencyCode() }}"/>
    <meta name ="keywords", content="{{$meta_tags}}, {{ config('app.name') }}">
@endsection
@push('styles')
    <link rel="stylesheet" href="{{asset(asset_path('frontend/amazy/css/page_css/product_details.css'))}}" />
    <link rel="stylesheet" href="{{asset(asset_path('frontend/default/css/lightbox.css'))}}" />
    @if(isRtl())
        <style>
            .zoomWindowContainer div {
                left: 0!important;
                right: 510px;
            }
            .product_details_part .cs_color_btn .radio input[type="radio"] + .radio-label:before {
                left: -1px !important;
            }
            @media (max-width: 970px) {
                .zoomWindowContainer div {
                    right: inherit!important;
                }
            }



        </style>
    @endif
    <style>
        .report-product {
                font-size: 12px;
                color: var(--base_color) !important;
                font-weight: 700;
                display: inline-flex;
                align-items: center;
                grid-gap: 10px;
                text-transform: uppercase;
            }

            .report-product:hover{
                color: var(--base_color) !important;
            }
    </style>
@endpush
@section('content')
    <!-- product_details_wrapper::start  -->
    <div class="product_details_wrapper">
        <div class="container">
            <div class="row">
                <div class="col-xl-9">
                    <div class="row">
                        <div class="col-lg-6 col-xl-6">
                            <div class="slider-container slick_custom_container mb_30" id="myTabContent">
                                <div class="slider-for gallery_large">
                                    @if(count($product->product->gallary_images) > 0)
                                        @foreach($product->product->gallary_images as $image)
                                            <div class="item-slick {{$product->product->gallary_images->first()->id == $image->id?'slick-current slick-active':''}}" id="thumb_{{$image->id}}">
                                                <img class="varintImg zoom_01" src="{{showImage($image->images_source)}}" data-zoom-image="{{showImage($image->images_source)}}" alt="{{$product->product_name}}" title="{{$product->product_name}}">
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="item-slick slick-current slick-active" id="thumb_{{$product->id}}">
                                            <img class="varintImg zoom_01" @if ($product->thum_img != null) data-zoom-image="{{showImage($product->thum_img)}}" @else data-zoom-image="{{showImage($product->product->thumbnail_image_source)}}" @endif @if ($product->thum_img != null) src="{{showImage($product->thum_img)}}" @else src="{{showImage($product->product->thumbnail_image_source)}}" @endif alt="{{$product->product_name}}" title="{{$product->product_name}}">
                                        </div>
                                    @endif
                                </div>
                                <div class="slider-nav">
                                    @if(count($product->product->gallary_images) > 0)
                                        @foreach($product->product->gallary_images as $i => $image)
                                            <div class="item-slick {{$i == 0?'slick-active slick-current':''}}">
                                                <img src="{{showImage($image->images_source)}}" alt="{{$product->product_name}}" title="{{$product->product_name}}">
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="item-slick slick-active slick-current">
                                            <img @if ($product->thum_img != null) src="{{showImage($product->thum_img)}}" @else src="{{showImage($product->product->thumbnail_image_source)}}" @endif alt="{{$product->product_name}}" title="{{$product->product_name}}">
                                        </div>
                                    @endif
                                </div>
                                <input type="hidden" id="product_id" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" id="maximum_order_qty" value="{{@$product->product->max_order_qty}}">
                                <input type="hidden" id="minimum_order_qty" value="{{@$product->product->minimum_order_qty}}">
                                <input type="hidden" name="thumb_image" id="thumb_image" value="{{showImage($product->thum_img ? $product->thum_img : $product->product->thumbnail_image_source)}}">
                            </div>
                        </div>
                        <div class="col-lg-6 col-xl-6">
                            <div class="product_content_details mb_20">
                                <div id="stock_div">
                                    @if ($product->stock_manage == 1 && @$product->skus->where('status',1)->first()->product_stock >= @$product->product->minimum_order_qty)
                                        <span class="stoke_badge">{{__('common.in_stock')}}</span>
                                    @elseif($product->stock_manage == 0)
                                        <span class="stoke_badge">{{__('common.in_stock')}}</span>
                                    @else
                                        <span class="stokeout_badge">{{__('amazy.Out of stock')}}</span>
                                    @endif
                                </div>
                                <h3>{{$product->product_name}}</h3>
                                @if(app('general_setting')->product_subtitle_show)
                                    @if($product->subtitle_1)
                                        <h5>{{$product->subtitle_1}}</h5>
                                    @endif
                                    @if($product->subtitle_2)
                                        <h5>{{$product->subtitle_2}}</h5>
                                    @endif
                                @endif
                                <div class="viendor_text d-flex align-items-center">
                                    <p class="stock_text"> <span class="text-uppercase">{{__('defaultTheme.sku')}}:</span> <span class="stock_value" id="sku_id_li"> {{@$product->skus->where('status',1)->first()->sku->sku??'-'}}</span></p>
                                    <p class="stock_text"> <span class="text-uppercase">{{__('common.category')}}:</span>
                                        @php
                                            $cates = count($product->product->categories);
                                        @endphp
                                        @foreach($product->product->categories as $key => $category)
                                            <span>{{$category->name}}</span>
                                            @if($key + 1 < $cates), @endif
                                        @endforeach
                                    </p>
                                </div>
                                <div class="viendor_text d-flex align-items-center">
                                    <p class="stock_text"> <span class="text-uppercase">{{__('defaultTheme.availability')}}:</span> <span class="stock_value" id="availability">
                                        @if ($product->stock_manage == 0)
                                        {{__('defaultTheme.unlimited')}}
                                        @else
                                            {{ $product->skus->where('status',1)->first()->product_stock }}
                                        @endif
                                    </span></p>
                                </div>
                                <div class="d-flex flex-wrap align-items-center">
                                    <div class="product_ratings mb-2">
                                        <div class="stars">
                                            <x-rating :rating="$rating"/>
                                        </div>
                                        <span>{{getNumberTranslate(sprintf("%.2f",$rating))}}/{{getNumberTranslate(5)}} ({{($total_review<10 && $total_review>0)?'0':''}}{{getNumberTranslate($total_review)}} {{__('defaultTheme.review')}})</span>
                                    </div>
                                    @if(isModuleActive('ClubPoint'))
                                    <div class="border-start ps-3 ms-3">
                                        <span class="d-flex align-items-center point">
                                            <svg width="16" height="14" viewBox="0 0 16 14" fill="none" >
                                                <path d="M15 7.6087V10.087C15 11.1609 12.4191 12.5652 9.23529 12.5652C6.05153 12.5652 3.47059 11.1609 3.47059 10.087V8.02174M3.71271 8.2357C4.42506 9.18404 6.628 10.0737 9.23529 10.0737C12.4191 10.0737 15 8.74704 15 7.60704C15 6.96683 14.1872 6.26548 12.9115 5.77313M12.5294 3.47826V5.95652C12.5294 7.03044 9.94847 8.43478 6.76471 8.43478C3.58094 8.43478 1 7.03044 1 5.95652V3.47826M6.76471 5.9433C9.94847 5.9433 12.5294 4.61661 12.5294 3.47661C12.5294 2.33578 9.94847 1 6.76471 1C3.58094 1 1 2.33578 1 3.47661C1 4.61661 3.58094 5.9433 6.76471 5.9433Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            {{getNumberTranslate(@$product->product->club_point)}}
                                        </span>
                                    </div>
                                    @endif
                                </div>
                                <div class="destils_prise_information_box mb_20">
                                    @if(isGuestAddtoCart() == true)
                                    <h2 class="pro_details_prise d-flex align-items-center  m-0">
                                        <span>
                                            {{getProductDiscountedPrice($product)}}
                                        </span>
                                    </h2>
                                    @endif
                                    <div class="pro_details_disPrise d-flex align-items-center gap_15">
                                        @if(isGuestAddtoCart() == true)
                                            <h4 class="discount_prise  m-0  ">
                                                <span class="text-decoration-line-through">
                                                    @if($product->hasDeal || $product->hasDiscount == 'yes')
                                                        <span>{{single_price($product->skus->max('sell_price'))}}</span>
                                                    @endif
                                                </span>
                                            </h4>
                                        @endif
                                        @if(isGuestAddtoCart() == true)
                                            @if(@$product->hasDeal)
                                                @if(@$product->hasDeal->discount > 0)
                                                    @if(@$product->hasDeal->discount_type == 0)
                                                        <span class="diccount_percents">
                                                            -{{getNumberTranslate(@$product->hasDeal->discount)}}%
                                                        </span>
                                                    @else
                                                        <span class="diccount_percents">
                                                            -{{single_price(@$product->hasDeal->discount)}}
                                                        </span>
                                                    @endif
                                                @endif
                                            @else
                                                @if(@$product->hasDiscount == 'yes')
                                                    @if($product->discount > 0)
                                                        @if($product->discount_type == 0)
                                                        <span class="diccount_percents">
                                                            -{{getNumberTranslate($product->discount)}}%
                                                        </span>
                                                        @else
                                                        <span class="diccount_percents">
                                                            -{{single_price($product->discount)}}
                                                        </span>
                                                        @endif
                                                    @endif
                                                @endif
                                            @endif
                                        @endif
                                    </div>
                                    <input type="hidden" name="product_sku_id" id="product_sku_id" value="{{$product->product->product_type == 1?$product->skus->where('status',1)->first()->id : $product->skus->where('status',1)->first()->id}}">
                                    <input type="hidden" name="seller_id" id="seller_id" value="{{$product->user_id}}">
                                    <input type="hidden" id="owner" value="{{encrypt($product->user_id)}}">
                                    <input type="hidden" name="stock_manage_status" id="stock_manage_status" value="{{$product->stock_manage}}">
                                    <p class="pro_details_text">
                                        <span class="text-uppercase">{{__('common.tag')}}:</span>
                                        @php
                                            $total_tag = count($product->product->tags);
                                        @endphp
                                        @foreach($product->product->tags as $key => $tag)
                                            <a class="tag_link" href="{{route('frontend.category-product',['slug' => $tag->name, 'item' =>'tag'])}}">{{$tag->name}}</a>
                                        @endforeach
                                    </p>
                                </div>
                                <input type="hidden" name="product_type" class="product_type" value="{{ $product->product->product_type }}">

                                @if($product->product->product_type == 2 && session()->get('item_details') != '')
                                    @foreach (session()->get('item_details') as $key => $item)
                                        @if ($item['attr_id'] === 1)
                                            <div class="product_color_varient mb_20">
                                                <h5 class="font_14 f_w_500 theme_text3  text-capitalize d-block mb_10" id="color_name">{{ $item['name'] }}: {{$item['value'][0]}} </h5>
                                                <div class="color_List d-flex gap_5 flex-wrap">
                                                    <input type="hidden" class="attr_value_name" name="attr_val_name[]" value="{{$item['value'][0]}}">
                                                    <input type="hidden" class="attr_value_id" name="attr_val_id[]" value="{{$item['id'][0]}}-{{$item['attr_id']}}">
                                                    @foreach ($item['value'] as $k => $value_name)
                                                        <label class="round_checkbox d-flex">
                                                            <input id="radio-{{$k}}" name="color_filt" class="attr_val_name  radio_{{ $item['id'][$k] }}" type="radio" color="color" @if ($k === 0) checked @endif data-value="{{ $item['id'][$k] }}" data-name="{{ $item['name'] }}" data-value-key="{{$item['attr_id']}}" value="{{ $value_name }}"/>
                                                            <span class="checkmark colors_{{$k}} class_color_{{ $item['code'][$k] }}">
                                                                <div class="check_bg_color"></div>
                                                            </span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                        @if ($item['attr_id'] != 1)
                                            <div class="product_color_varient mb_20">
                                                <h5 class="font_14 f_w_500 theme_text3  text-capitalize d-block mb_10" id="size_name{{$key}}">{{$item['name']}}: {{$item['value'][0]}}</h5>
                                                <div class="color_List d-flex gap_5 flex-wrap">
                                                    <input type="hidden" class="attr_value_name" data-name="{{ $item['name'] }}" name="attr_val_name[]" value="{{$item['value'][0]}}">
                                                    <input type="hidden" class="attr_value_id" name="attr_val_id[]" value="{{$item['id'][0]}}-{{$item['attr_id']}}">
                                                    @foreach ($item['value'] as $m => $value_name)
                                                        <a class="attr_val_name size_btn not_111 @if ($m === 0) selected_btn @endif" color="not" id="attr_val_variant_id_{{ $item['id'][$m] }}" data-name="{{ $item['name'] }}" data-value-key="{{$item['attr_id']}}" data-value="{{ $item['id'][$m] }}">{{ $value_name }}</a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                    @php
                                        $variant_images = [];
                                        $variant_skus = [];
                                        foreach($product->skus->where('status',1) as $sku){
                                            if(@$sku->sku->variant_image){
                                                $variant_images[] = $sku->sku->variant_image;
                                                $variant_skus[] = $sku->sku->sku;
                                                $variant_product_sku_ids[] = $sku->product_sku_id;
                                            }
                                        }
                                    @endphp
                                    @if(count($variant_images) > 0)
                                    <div class="single_details_content variant_image d-flex flex-wrap align-items-center mb-2 mb-md-3">
                                        <h5>{{__('amazy.Variant images')}}:</h5>
                                        @if(count($variant_images) > 5)
                                            <div class="variant-slider owl-carousel">
                                                @foreach($variant_images as $variant_key => $variant_image)
                                                    <div class="sku_img_div @if($loop->first) active @endif " id="{{$variant_skus[$variant_key]}}" data-id="{{$variant_product_sku_ids[$variant_key]}}" onclick="changeProdDetailsByVariantImg(this)">
                                                        <img src="{{showImage($variant_image)}}" title="{{ $variant_skus[$variant_key] }}" class="img-fluid p-1 var_img_sources " alt="{{ $variant_skus[$variant_key] }}" data-id="{{$variant_skus[$variant_key]}}"/>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                        <div class="img_div_width d-flex">
                                            @foreach($variant_images as $variant_key => $variant_image)
                                             <div class="sku_img_div @if($loop->first) active @endif " id="{{$variant_skus[$variant_key]}}"  data-id="{{$variant_product_sku_ids[$variant_key]}}">
                                                <img src="{{showImage($variant_image)}}" title="{{ $variant_skus[$variant_key] }}" class="img-fluid p-1 var_img_sources " alt="{{ $variant_skus[$variant_key] }}" data-id="{{$variant_skus[$variant_key]}}"/>
                                            </div>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                @endif
                                @endif
                                <!--show wholesale price -->
                                @if(isModuleActive('WholeSale'))
                                    <div class="{{ @$product->skus->where('status',1)->first()->wholeSalePrices->count() ? 'd-flex':'d-none'}}">
                                        <table class="table-sm append_w_s_p_tbl mb-3" width="100%">
                                            <thead>
                                            <tr class="border-bottom ">
                                                <td  class="text-left">
                                                    <label for="" class="control-label">{{__('common.Min QTY')}}</label>
                                                </td>
                                                <td class="text-left">
                                                    <label for="" class="control-label">{{__('common.Max QTY')}}</label>
                                                </td>
                                                <td class="text-left">
                                                    <label for="" class="control-label">{{__('common.unit_price')}}</label>
                                                </td>
                                            </tr>
                                            </thead>
                                            <tbody id="append_w_s_p_all">
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                                <div class="product_info">
                                    <div class="single_pro_varient">
                                        <h5 class="font_14 f_w_500 theme_text3 " >{{__('common.quantity')}}:</h5>
                                        <div class="product_number_count mr_5" data-target="amount-1">
                                            <span class="count_single_item inumber_decrement qtyChange" data-value="-"> <i class="ti-minus"></i></span>
                                            <input name="qty" id="qty" class="count_single_item input-number qty" type="text" data-value="{{@$product->product->minimum_order_qty}}" value="{{getNumberTranslate(@$product->product->minimum_order_qty)}}">
                                            <span class="count_single_item number_increment qtyChange" data-value="+"> <i class="ti-plus"></i></span>
                                        </div>
                                    </div>
                                    <input type="hidden" name="base_sku_price" id="base_sku_price" value="
                                        @if(@$product->hasDeal)
                                            {{ selling_price(@$product->skus->where('status',1)->first()->sell_price,@$product->hasDeal->discount_type,@$product->hasDeal->discount) }}
                                        @else
                                            @if($product->hasDiscount == 'yes')
                                            {{selling_price(@$product->skus->where('status',1)->first()->sell_price,@$product->discount_type,@$product->discount)}}
                                            @else
                                                {{@$product->skus->where('status',1)->first()->sell_price}}
                                            @endif
                                        @endif
                                    ">
                                    <input type="hidden" name="final_price" id="final_price" value="
                                        @if(@$product->hasDeal)
                                            {{ selling_price(@$product->skus->where('status',1)->first()->sell_price,@$product->hasDeal->discount_type,@$product->hasDeal->discount) }}
                                        @else
                                            @if($product->hasDiscount == 'yes')
                                            {{selling_price(@$product->skus->where('status',1)->first()->sell_price,@$product->discount_type,@$product->discount)}}
                                            @else
                                                {{@$product->skus->where('status',1)->first()->sell_price}}
                                            @endif
                                        @endif
                                    ">

                                    @if(isGuestAddtoCart() == true)
                                    <h5 class="mb-0">{{__('common.total')}}:
                                        <span id="total_price">
                                            @if(@$product->hasDeal)
                                                {{single_price(selling_price(@$product->skus->where('status',1)->first()->sell_price,@$product->hasDeal->discount_type,@$product->hasDeal->discount) * $product->product->minimum_order_qty)}}
                                            @else
                                                @if($product->hasDiscount == 'yes')
                                                    {{single_price(selling_price(@$product->skus->where('status',1)->first()->sell_price,@$product->discount_type,@$product->discount) * $product->product->minimum_order_qty)}}
                                                @else
                                                    {{single_price(@$product->skus->where('status',1)->first()->sell_price * $product->product->minimum_order_qty)}}
                                                @endif
                                            @endif
                                        </span>
                                    </h5>
                                    @endif

                                    @if(isModuleActive('CheckPincode') && $pincodeConfig->pincode_check_system_status==1)
                                        <div class="row mt_30 form-inline" id="checkpincode_div">
                                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                                <div class="form-group ">
                                                    <input type="text" class="form-control" id="check_pincode" placeholder="CHECK PINCODE" style="border: none;border-bottom: 1px solid #ced4da;border-radius:0px;padding-top:20px; text-align:center;">
                                                </div>
                                            </div>
                                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 mt-3 mt-md-0">
                                                <button type="button" class="amaz_primary_btn3" id="check_pincode_btn">{{__('checkpincode.check')}}</button>
                                            </div>
                                        </div>
                                        <div class="row d-none mt_10 availablity_show_div">
                                            <div class="col-lg-12">
                                                <p>{{__('checkpincode.available_at')}} <span id="pin_code_area"></span>.</p>

                                                <p class="pdelivery d-none">{{__('amazy.Delivery within')}}: <span id="pin_code_delivery"></span>.</p>

                                            </div>
                                        </div>
                                        <div class="row mt_10 not_serve d-none">
                                            <p>{{__('checkpincode.oops_we_are_not_currently_servicing_your_area')}}</p>
                                        </div>
                                    @endif

                                        <!-- *p* -->
                                    @if(isGuestAddtoCart() == true)
                                    <div class="row mt_30 " id="add_to_cart_div">
                                            @if ($product->stock_manage == 1 && $product->skus->where('status',1)->first()->product_stock >= $product->product->minimum_order_qty)
                                                <div class="col-4">
                                                    <button type="button" id="add_to_cart_btn" class="amaz_primary_btn style2 mb_20 add_to_cart add_to_cart_btn text-uppercase flex-fill text-center w-100">
                                                        {{__('common.add_to_cart')}}
                                                    </button>
                                                </div>
                                                <div class="col-4">
                                                    <button type="button" id="butItNow" class="amaz_primary_btn3 mb_20 w-100 text-center justify-content-center text-uppercase buy_now_btn" data-id="{{$product->id}}" data-type="product">
                                                        {{__('common.buy_now')}}
                                                    </button>
                                                </div> 
                                                <div class="col-4">
                                                    <button type="button" class="amaz_primary_btn3 mb_20 w-100 text-center justify-content-center text-uppercase customize_btn" data-bs-toggle="modal" data-bs-target="#customizeModal">
                                                        Customize
                                                    </button>
                                                </div>
                                            @elseif($product->stock_manage == 0)
                                                <div class="col-4">
                                                    <button type="button" id="add_to_cart_btn" class="amaz_primary_btn style2 mb_20 add_to_cart text-uppercase add_to_cart_btn flex-fill text-center w-100">
                                                        {{__('common.add_to_cart')}}
                                                    </button>
                                                </div>
                                                <div class="col-4">
                                                    <button type="button" id="butItNow" class="amaz_primary_btn3 mb_20 w-100 text-center justify-content-center text-uppercase buy_now_btn" data-id="{{$product->id}}" data-type="product">
                                                        {{__('common.buy_now')}}
                                                    </button>
                                                </div>
                                                <div class="col-4">
                                                    <button type="button" class="amaz_primary_btn3 mb_20 w-100 text-center justify-content-center text-uppercase customize_btn" data-bs-toggle="modal" data-bs-target="#customizeModal">
                                                        Customize
                                                    </button>
                                                </div>
                                            @else
                                                <div class="col-6">
                                                    <button type="button" disabled class="amaz_primary_btn style2 mb_20 add_to_cart text-uppercase flex-fill text-center w-100">
                                                        {{__('defaultTheme.out_of_stock')}}
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                        <!-- @include('frontend.amazy.pages.customize_modal') -->
                                        
                                        <!-- Don't Toutch the code below -->
                                        <div class="add_wish_compare d-flex alingn-items-center mb_20">
                                            <a id="wishlist_btn" data-product_id="{{$product->id}}" data-seller_id="{{$product->user_id}}" class="single_wish_compare text-uppercase text-nowrap cursor_pointer">
                                                <i class="far fa-heart"></i> {{__('defaultTheme.add_to_wishlist')}}
                                            </a>
                                            <a id="add_to_compare_btn_modify" data-product_sku_id="#product_sku_id" data-product_type="{{$product->product->product_type}}" class="single_wish_compare text-uppercase text-nowrap cursor_pointer">
                                                <i class="ti-control-shuffle"></i> {{__('defaultTheme.add_to_compare')}}
                                            </a>
                                            @if(!empty($product->seller) && $product->seller->role_id !=  1 && app('general_setting')->product_report == 1)

                                                <a class="report-product" data-product-id='{{ $product->product_id }}' href="javascript:void(0)">
                                                    <i class="fas fa-ban"></i>
                                                    {{ __('product.report_this_product') }}
                                                </a>
                                            @endif
                                        </div>
                                    @else
                                    <div class="row mt_30 " id="add_to_cart_div">
                                        <div class="col-md-12">
                                            <a href="{{ url('/login') }}" class="amaz_primary_btn w-100">
                                                {{__('defaultTheme.login_to_order')}}
                                            </a>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @php
                                $both_buy_product = null;
                                if(@$product->product->display_in_details == 1){
                                    if($product->up_sales->count()){
                                        $both_buy_product = @$product->up_sales[0]->up_seller_products[0];
                                    }
                                }else{
                                    if($product->cross_sales->count()){
                                        $both_buy_product = @$product->cross_sales[0]->cross_seller_products[0];
                                    }
                                }
                            @endphp
                            @if($product->stock_manage == 1 && $product->skus->where('status',1)->first()->product_stock >= $product->product->minimum_order_qty || $product->stock_manage == 0)
                                @if($both_buy_product && $both_buy_product->stock_manage == 1 && $both_buy_product->skus->first()->product_stock >= $both_buy_product->product->minimum_order_qty || $both_buy_product && $both_buy_product->stock_manage == 0)
                                <div class="product_details_sujjetion">
                                    <h4 class="font_14 f_w_700 text-uppercase mb_12 lh-1">{{__('amazy.YOU CAN ALSO BUY')}}:</h4>
                                    <div class="product_details_sujjetion_box">
                                        <a href="{{singleProductURL(@$both_buy_product->seller->slug, $both_buy_product->slug)}}" class="product_details_sujjetion_single d-flex align-items-center gap_15">
                                            @php
                                                if(@$product->hasDeal){
                                                    $both_buy_price = selling_price(@$both_buy_product->skus->first()->sell_price,@$both_buy_product->hasDeal->discount_type,@$both_buy_product->hasDeal->discount);
                                                }else{
                                                    if(@$product->hasDiscount == 'yes'){
                                                        $both_buy_price = selling_price(@$both_buy_product->skus->first()->sell_price,@$both_buy_product->discount_type,@$both_buy_product->discount);
                                                    }else{
                                                        $both_buy_price = @$both_buy_product->skus->first()->sell_price;
                                                    }
                                                }
                                            @endphp
                                            <input type="hidden" id="both_buy_price" value="{{$both_buy_price}}">
                                            <div class="thumb both_buy">
                                                <img src="
                                                @if(@$both_buy_product->thum_img != null)
                                                    {{showImage(@$both_buy_product->thum_img)}}
                                                @else
                                                    {{showImage(@$both_buy_product->product->thumbnail_image_source)}}
                                                @endif
                                                " alt="{{@$both_buy_product->product->product_name}}" title="{{@$both_buy_product->product->product_name}}">
                                            </div>
                                            <div class="product_details_sujjetion_content">
                                                <h4 class="fs-6 f_w_700">@if ($both_buy_product->product_name) {{ textLimit($both_buy_product->product_name,28) }} @else {{textLimit(@$both_buy_product->product->product_name,28)}} @endif</h4>
                                                <p class="font_14 f_w_500 mb-0 lh-1">
                                                    {{single_price($both_buy_price)}}
                                                </p>
                                            </div>
                                        </a>
                                        <div class="product_details_sujjetion_total d-flex align-items-center gap_15">
                                            <div class="product_details_sujjetion_left flex-fill">
                                                <span class="font_12 f_w_500 d-block">{{__('common.total_price')}}:</span>
                                                <h4 id="both_buy_price_show" class="font_16 f_w_700 m-0 lh-1"></h4>
                                            </div>
                                            <a href="#" class="amaz_primary_btn style3 text-uppercase" id="both_buy_btn" data-sku_id="{{ @$both_buy_product->skus->first()->id }}"
                                                data-seller_id="{{ $both_buy_product->user_id }}" data-product_id="{{$both_buy_product->id}}" data-qty="{{@$both_buy_product->product->minimum_order_qty}}" >{{__('amazy.Buy Both')}}</a>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endif
                            @php
                                $sharelinks = Share::currentPage()->facebook()->twitter()->linkedin()->whatsapp()->telegram()->reddit()->getRawLinks();
                            @endphp
                            <div class="contact_wiz_box mt_20">
                                <div id="TabbyPromo" class="w-100 mt-4 mb-4"></div>
                                <span class="contact_box_title font_16 f_w_500 d-block lh-1 ">{{__('defaultTheme.share_on')}}:</span>
                                <div class="contact_link">
                                   <a target="_blank" href="{{ $sharelinks['facebook'] }}">
                                       <i class="fab fa-facebook"></i>
                                   </a>
                                   <a target="_blank" href="{{ $sharelinks['twitter'] }}">
                                       <i class="fab fa-twitter"></i>
                                   </a>
                                   <a target="_blank" href="{{ $sharelinks['linkedin'] }}">
                                       <i class="fab fa-linkedin-in"></i>
                                   </a>
                                   <a target="_blank" href="{{ $sharelinks['whatsapp'] }}">
                                       <i class="fab fa-whatsapp"></i>
                                   </a>
                                   <a target="_blank" href="{{ $sharelinks['telegram'] }}">
                                       <i class="fab fa-telegram-plane"></i>
                                   </a>
                                    <a target="_blank" href="{{ $sharelinks['reddit'] }}">
                                        <i class="ti-reddit"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                                <div class="col-12">
                                    <div class="product_details_dec mb_76">
                                        <div class="product_details_dec_header">
                                            <h4 class="font_20 f_w_400 m-0 ">{{__('common.description')}}</h4>
                                        </div>
                                        <div class="product_details_dec_body">
                                            <div class="product_description_container">
                                                <div class="contents">
                                                    @php
                                                        echo $product->product->description;
                                                    @endphp
                                                </div>
                                            </div>
                                            <div class="col-12 show_full_btn d-none">
                                                <button id="show_full_details" class="show_less amaz_primary_btn style3 text-uppercase">Show More</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="product_details_dec mb_76">
                                        <div class="product_details_dec_header">
                                            <h4 class="font_20 f_w_400 m-0 ">{{__('defaultTheme.product_specifications')}}</h4>
                                        </div>
                                        <div class="product_details_dec_body">
                                            <div class="single_desc style2 mb_20">
                                                <p class="f_w_500 m-0">{{ __('common.brand') }}: {{@$product->product->brand->name}}</p>
                                                <p class="f_w_500 m-0">{{ __('common.model_number') }}: {{@$product->product->model_number}}</p>
                                                <p class="f_w_500 m-0">{{ __('common.availability') }}:
                                                    @if ($product->stock_manage == 1 && $product->skus->where('status',1)->first()->product_stock >= $product->product->minimum_order_qty)
                                                        {{ __('common.in_stock') }}
                                                    @elseif($product->stock_manage == 0)
                                                        {{ __('common.in_stock') }}
                                                    @else
                                                        {{__('defaultTheme.out_of_stock')}}
                                                    @endif
                                                </p>
                                                <p class="f_w_500 m-0">{{ __('common.minimum_order_quantity') }}: {{getNumberTranslate(@$product->product->minimum_order_qty)}}</p>
                                                <p class="f_w_500 m-0">{{ __('common.maximum_order_quantity') }}: {{getNumberTranslate(@$product->product->max_order_qty)}}</p>
                                                <p class="f_w_500 m-0">{{ __('common.listed_date') }}: {{dateConvert(@$product->product->created_at)}}</p>
                                            </div>
                                            @php
                                                echo $product->product->specification;
                                            @endphp
                                        </div>
                                    </div>
                                    @if (!empty($product->product->pdf))
                                    <div class="product_details_dec mb_76">
                                        <div class="product_details_dec_header">
                                            <h4 class="font_20 f_w_400 m-0 ">{{ __('product.pdf_specifications') }}</h4>
                                        </div>
                                        <div class="product_details_dec_body">
                                            <a class="anchore_color" href="{{ asset(asset_path($product->product->pdf)) }}" download>{{ __('product.download_file') }}</a>
                                        </div>
                                    </div>
                                    @endif
                                    @if ($product->product->video_link)
                                        <div class="product_details_dec mb_76">
                                            <div class="product_details_dec_header">
                                                <h4 class="font_20 f_w_400 m-0 ">{{__('defaultTheme.video')}}</h4>
                                            </div>
                                            <div class="product_details_dec_body">
                                                <div class="product_details">
                                                    @if ($product->product->video_provider == 'youtube')
                                                        @php
                                                            $link = str_replace('watch?v=','embed/',$product->product->video_link);
                                                        @endphp
                                                        <iframe src="{{ $link }}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                                    @endif
                                                    @if ($product->product->video_provider == 'daily_motion')
                                                        @php
                                                            if(strpos($product->product->video_link, 'dai.ly') != false){
                                                                $link = str_replace('https://dai.ly/','https://www.dailymotion.com/embed/video/',$product->product->video_link);
                                                            }else{
                                                                $link = str_replace('https://www.dailymotion.com/video/','https://www.dailymotion.com/embed/video/',$product->product->video_link);
                                                            }
                                                        @endphp
                                                        <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;">
                                                            <iframe style="width:100%;height:100%;position:absolute;left:0px;top:0px;overflow:hidden" frameborder="0" type="text/html"
                                                            src="{{$link}}" width="100%" height="100%" allowfullscreen allow="autoplay"> </iframe>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                @if($recent_viewed_products->count())
                                    <div class="col-12">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="section__title d-flex align-items-center gap-3 mb_30">
                                                    <h3 class="m-0 flex-fill">{{__('amazy.Customers who viewed this also viewed')}}</h3>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            @foreach($recent_viewed_products as $recent_viewed_product)
                                                <div class="col-xl-4 col-lg-4 col-md-6 d-flex">
                                                    <div class="product_widget5 mb_30 style5 w-100">
                                                        <div class="product_thumb_upper">
                                                            @php
                                                                if(@$recent_viewed_product->thum_img != null){
                                                                    $thumbnail = showImage(@$recent_viewed_product->thum_img);
                                                                }else {
                                                                    $thumbnail = showImage(@$recent_viewed_product->product->thumbnail_image_source);
                                                                }

                                                                $price_qty = getProductDiscountedPrice(@$recent_viewed_product);
                                                                $showData = [
                                                                    'name' => @$recent_viewed_product->product_name,
                                                                    'url' => singleProductURL(@$recent_viewed_product->seller->slug, @$recent_viewed_product->slug),
                                                                    'price' => $price_qty,
                                                                    'thumbnail' => $thumbnail
                                                                ];
                                                            @endphp
                                                            <a href="{{singleProductURL($recent_viewed_product->seller->slug, $recent_viewed_product->slug)}}" class="thumb">
                                                                @if(app('general_setting')->lazyload == 1)
                                                                 <img data-src="{{$thumbnail}}" src="{{showImage(themeDefaultImg())}}" alt="{{@$recent_viewed_product->product_name}}" title="{{@$recent_viewed_product->product_name}}" class="lazyload">
                                                                @else
                                                                 <img   src="{{$thumbnail}}" alt="{{@$recent_viewed_product->product_name}}" title="{{@$recent_viewed_product->product_name}}" >
                                                                @endif
                                                            </a>
                                                            @if(isGuestAddtoCart())
                                                            <div class="product_action">
                                                                <a href="" class="addToCompareFromThumnail" data-producttype="{{ @$recent_viewed_product->product->product_type }}" data-seller={{ $recent_viewed_product->user_id }} data-product-sku={{ @$recent_viewed_product->skus->first()->id }} data-product-id={{ $recent_viewed_product->id }}>
                                                                    <i class="ti-control-shuffle" title="{{ __('defaultTheme.compare') }}"></i>
                                                                </a>
                                                                <a href="" class="add_to_wishlist {{$recent_viewed_product->is_wishlist() == 1?'is_wishlist':''}}" id="wishlistbtn_{{$recent_viewed_product->id}}" data-product_id="{{$recent_viewed_product->id}}" data-seller_id="{{$recent_viewed_product->user_id}}">
                                                                    <i class="far fa-heart" title="{{ __('defaultTheme.wishlist') }}"></i>
                                                                </a>
                                                                <a class="quickView" data-product_id="{{$recent_viewed_product->id}}" data-type="product">
                                                                    <i class="ti-eye" title="{{ __('defaultTheme.quick_view') }}"></i>
                                                                </a>
                                                            </div>
                                                            @endif
                                                            <div class="product_badge">
                                                                @if(isGuestAddtoCart())
                                                                    @if($recent_viewed_product->hasDeal)
                                                                        @if($recent_viewed_product->hasDeal->discount >0)
                                                                            <span class="d-flex align-items-center discount">
                                                                                @if($recent_viewed_product->hasDeal->discount >0)
                                                                                    @if($recent_viewed_product->hasDeal->discount_type ==0)
                                                                                        {{getNumberTranslate($recent_viewed_product->hasDeal->discount)}} % {{__('common.off')}}
                                                                                    @else
                                                                                        {{single_price($recent_viewed_product->hasDeal->discount)}} {{__('common.off')}}
                                                                                    @endif

                                                                                @endif
                                                                            </span>
                                                                        @endif
                                                                    @else
                                                                        @if($recent_viewed_product->hasDiscount == 'yes')
                                                                        @if($recent_viewed_product->discount > 0)
                                                                            <span class="d-flex align-items-center discount">
                                                                                @if($recent_viewed_product->discount >0)
                                                                                    @if($recent_viewed_product->discount_type ==0)
                                                                                        {{getNumberTranslate($recent_viewed_product->discount)}} % {{__('common.off')}}
                                                                                    @else
                                                                                        {{single_price($recent_viewed_product->discount)}} {{__('common.off')}}
                                                                                    @endif
                                                                                @endif
                                                                            </span>
                                                                        @endif
                                                                        @endif
                                                                    @endif
                                                                @endif
                                                                @if(isModuleActive('ClubPoint'))
                                                                <span class="d-flex align-items-center point">
                                                                    <svg width="16" height="14" viewBox="0 0 16 14" fill="none" >
                                                                        <path d="M15 7.6087V10.087C15 11.1609 12.4191 12.5652 9.23529 12.5652C6.05153 12.5652 3.47059 11.1609 3.47059 10.087V8.02174M3.71271 8.2357C4.42506 9.18404 6.628 10.0737 9.23529 10.0737C12.4191 10.0737 15 8.74704 15 7.60704C15 6.96683 14.1872 6.26548 12.9115 5.77313M12.5294 3.47826V5.95652C12.5294 7.03044 9.94847 8.43478 6.76471 8.43478C3.58094 8.43478 1 7.03044 1 5.95652V3.47826M6.76471 5.9433C9.94847 5.9433 12.5294 4.61661 12.5294 3.47661C12.5294 2.33578 9.94847 1 6.76471 1C3.58094 1 1 2.33578 1 3.47661C1 4.61661 3.58094 5.9433 6.76471 5.9433Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                                                    </svg>
                                                                    {{ @$recent_viewed_product->product->club_point }}
                                                                </span>
                                                                @endif
                                                                @if(isModuleActive('WholeSale') && @$recent_viewed_product->skus->first()->wholeSalePrices->count())
                                                                    <span class="d-flex align-items-center sale">{{__('common.wholesale')}}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="product_star mx-auto">
                                                            @php
                                                                $reviews = @$recent_viewed_product->reviews->where('status', 1)->pluck('rating');

                                                                if (count($reviews) > 0) {
                                                                    $value = 0;
                                                                    $rating = 0;
                                                                    foreach ($reviews as $review) {
                                                                        $value += $review;
                                                                    }
                                                                    $rating = $value / count($reviews);
                                                                    $total_review = count($reviews);
                                                                } else {
                                                                    $rating = 0;
                                                                    $total_review = 0;
                                                                }
                                                            @endphp
                                                            <x-rating :rating="$rating" />
                                                        </div>
                                                        <div class="product__meta text-center">
                                                            <span class="product_banding ">{{ @$recent_viewed_product->brand->name ?? " " }}</span>
                                                            <a href="{{singleProductURL(@$recent_viewed_product->seller->slug, $recent_viewed_product->slug)}}">
                                                                <h4>@if ($recent_viewed_product->product_name) {{ textLimit(@$recent_viewed_product->product_name, 50) }} @else {{ textLimit(@$recent_viewed_product->product->product_name, 50) }} @endif</h4>
                                                            </a>

                                                        @if(isGuestAddtoCart())
                                                            <div class="product_price d-flex align-items-center justify-content-between flex-wrap">
                                                                <a class="amaz_primary_btn addToCartFromThumnail" data-producttype="{{ @$recent_viewed_product->product->product_type }}" data-seller={{ $recent_viewed_product->user_id }} data-product-sku={{ @$recent_viewed_product->skus->first()->id }}
                                                                    @if (@$recent_viewed_product->hasDeal)
                                                                        data-base-price={{ selling_price(@$recent_viewed_product->skus->first()->sell_price,@$recent_viewed_product->hasDeal->discount_type,@$recent_viewed_product->hasDeal->discount) }}
                                                                    @else
                                                                        @if (@$recent_viewed_product->hasDiscount == 'yes')
                                                                            data-base-price={{ selling_price(@$recent_viewed_product->skus->first()->sell_price,@$recent_viewed_product->discount_type,@$recent_viewed_product->discount) }}
                                                                        @else
                                                                            data-base-price={{ @$recent_viewed_product->skus->first()->sell_price }}
                                                                        @endif
                                                                    @endif
                                                                    data-shipping-method=0
                                                                    data-product-id={{ $recent_viewed_product->id }}
                                                                    data-stock_manage="{{$recent_viewed_product->stock_manage}}"
                                                                    data-stock="{{@$recent_viewed_product->skus->first()->product_stock}}"
                                                                    data-min_qty="{{@$recent_viewed_product->product->minimum_order_qty}}"
                                                                    data-prod_info="{{ json_encode($showData) }}"
                                                                    >
                                                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" >
                                                                        <path d="M0.464844 1.14286C0.464844 0.78782 0.751726 0.5 1.10561 0.5H1.58256C2.39459 0.5 2.88079 1.04771 3.15883 1.55685C3.34414 1.89623 3.47821 2.28987 3.58307 2.64624C3.61147 2.64401 3.64024 2.64286 3.66934 2.64286H14.3464C15.0557 2.64286 15.5679 3.32379 15.3734 4.00811L13.8119 9.50163C13.5241 10.5142 12.6019 11.2124 11.5525 11.2124H6.47073C5.41263 11.2124 4.48508 10.5028 4.20505 9.47909L3.55532 7.10386L2.48004 3.4621L2.47829 3.45572C2.34527 2.96901 2.22042 2.51433 2.03491 2.1746C1.85475 1.84469 1.71115 1.78571 1.58256 1.78571H1.10561C0.751726 1.78571 0.464844 1.49789 0.464844 1.14286ZM4.79882 6.79169L5.44087 9.1388C5.56816 9.60414 5.98978 9.92669 6.47073 9.92669H11.5525C12.0295 9.92669 12.4487 9.60929 12.5795 9.14909L14.0634 3.92857H3.95529L4.78706 6.74583C4.79157 6.76109 4.79548 6.77634 4.79882 6.79169ZM7.72683 13.7857C7.72683 14.7325 6.96184 15.5 6.01812 15.5C5.07443 15.5 4.30942 14.7325 4.30942 13.7857C4.30942 12.8389 5.07443 12.0714 6.01812 12.0714C6.96184 12.0714 7.72683 12.8389 7.72683 13.7857ZM6.4453 13.7857C6.4453 13.5491 6.25405 13.3571 6.01812 13.3571C5.7822 13.3571 5.59095 13.5491 5.59095 13.7857C5.59095 14.0224 5.7822 14.2143 6.01812 14.2143C6.25405 14.2143 6.4453 14.0224 6.4453 13.7857ZM13.7073 13.7857C13.7073 14.7325 12.9423 15.5 11.9986 15.5C11.0549 15.5 10.2899 14.7325 10.2899 13.7857C10.2899 12.8389 11.0549 12.0714 11.9986 12.0714C12.9423 12.0714 13.7073 12.8389 13.7073 13.7857ZM12.4258 13.7857C12.4258 13.5491 12.2345 13.3571 11.9986 13.3571C11.7627 13.3571 11.5714 13.5491 11.5714 13.7857C11.5714 14.0224 11.7627 14.2143 11.9986 14.2143C12.2345 14.2143 12.4258 14.0224 12.4258 13.7857Z" fill="currentColor"/>
                                                                    </svg>
                                                                    {{__('defaultTheme.add_to_cart')}}
                                                                </a>
                                                                <p>
                                                                    @if (getProductwitoutDiscountPrice(@$recent_viewed_product) != single_price(0))
                                                                        <del>
                                                                            {{getProductwitoutDiscountPrice(@$recent_viewed_product)}}
                                                                        </del>
                                                                     @endif
                                                                    <strong>
                                                                        {{getProductDiscountedPrice(@$recent_viewed_product)}}
                                                                    </strong>
                                                                </p>
                                                            </div>
                                                        @else
                                                        <div class="product_price d-flex align-items-center justify-content-between flex-wrap">
                                                            <a class="amaz_primary_btn w-100" style="text-indent: 0;" href="{{ url('/login') }}">

                                                                {{__('defaultTheme.login_to_order')}}
                                                            </a>
                                                        </div>
                                                        @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                <div class="col-12" id="Reviews">
                                    @include(theme('partials._product_review_with_paginate'),['reviews' => @$product->ActiveReviewsWithPaginate, 'all_reviews' => $product->reviews])
                                </div>
                    </div>
                </div>

                <div class="col-xl-3">
                    @php
                        $free_shipping = \Modules\Shipping\Entities\ShippingMethod::where('request_by_user', $product->user_id)->where('is_active', 1)->where('id','>', 1)->where('cost', 0)->first();
                    @endphp
                    @if($free_shipping)
                        <div class="amazcart_delivery_wiz mb_20">
                            <div class="amazcart_delivery_wiz_head">
                                <h4 class="font_18 f_w_700 m-0">{{__('amazy.Delivery Info')}}</h4>
                            </div>
                            <div class="amazcart_delivery_wiz_body">
                                <h4 class="font_16 f_w_700 mb_6">{{$free_shipping->method_name}}</h4>
                                <p class="delivery_text font_14 f_w_400">{{__('common.free_shipping_on')}} {{$free_shipping->method_name}} {{__('common.starts from order amount')}} {{single_price($free_shipping->minimum_shopping)}}</p>
                            </div>
                        </div>
                    @endif
                    <div class="amazcart_delivery_wiz mb_20">
                        <div class="amazcart_delivery_wiz_head">
                            <h4 class="font_18 f_w_700 m-0">{{__('common.choose_your_location')}}</h4>
                        </div>
                        <div class="amazcart_delivery_wiz_body">
                            <div class="loc_city_selectBox d-flex flex-column">
                                <div class="selectBox_box ">
                                    @php
                                        if(@$product->seller->role_id == 1){
                                            $country_id = app('general_setting')->country_id;
                                            $city_id = app('general_setting')->city_id;
                                        }else{
                                            $country_id = @$product->seller->SellerBusinessInformation->business_country;
                                            $city_id = @$product->seller->SellerBusinessInformation->business_city;
                                        }
                                        $country = Modules\Setup\Entities\Country::find($country_id);
                                    @endphp
                                    <select class="amaz_select2 mb_10 w-100" id="select_city" name="select_city">
                                        <option data-display="Choose City" selected disabled>{{__('common.choose_city')}}</option>
                                        @if($country)
                                        @foreach($country->cities as $city)
                                            <option value="{{$city->id}}" {{($city->id == $city_id)?'selected':''}}>{{$city->name}}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="selectBox_box">
                                    @php
                                        $pickup_locations = \Modules\Shipping\Entities\PickupLocation::where('created_by', $product->user_id)->where('status', 1)->get();
                                    @endphp
                                    <select class="amaz_select2 w-100" id="selectPickup">
                                        <option data-display="Choose pickup location" disabled>{{__('amazy.Choose pickup location')}}</option>
                                        @if($pickup_locations)
                                        @foreach($pickup_locations as $pickup_location)
                                            <option value="{{$pickup_location->id}}" {{$pickup_location->is_default?'selected':''}}>{{$pickup_location->address}}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="amazcart_delivery_wiz_sep d-flex gap_15 mb_10">
                                <div class="icon d-flex align-items-center justify-content-center ">
                                    <img src="{{url('/')}}/public/frontend/amazy/img/product_details/details_car.svg" alt="{{__('amazy.Door Delivery')}}" title="{{__('amazy.Door Delivery')}}">
                                </div>
                                <div class="amazcart_delivery_wiz_content">
                                    <h4 class="font_16 f_w_700 mb_6">{{__('amazy.Door Delivery')}}</h4>
                                    <p class="delivery_text font_14 f_w_400" id="door_delivery"></p>
                                </div>
                            </div>
                            <div class="amazcart_delivery_wiz_sep d-flex gap_15 ">
                                <div class="icon d-flex align-items-center justify-content-center ">
                                    <img src="{{url('/')}}/public/frontend/amazy/img/product_details/details_pickup.svg" alt="{{__('amazy.Pickup Location')}}" title="{{__('amazy.Pickup Location')}}">
                                </div>
                                <div class="amazcart_delivery_wiz_content">
                                    <h4 class="font_16 f_w_700 mb_6">{{__('amazy.Pickup Location')}}</h4>
                                    <p class="delivery_text font_14 f_w_400 mb-0" id="pickup_location"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="amazcart_delivery_wiz mb_20">
                        <div class="amazcart_delivery_wiz_body">
                            <div class="amazcart_delivery_wiz_sep d-flex gap_15 mb_10">
                                <div class="icon d-flex align-items-center justify-content-center ">
                                    <img src="{{url('/')}}/public/frontend/amazy/img/product_details/details_pickup.svg" alt="{{__('amazy.Return Policy')}}" title="{{__('amazy.Return Policy')}}">
                                </div>
                                <div class="amazcart_delivery_wiz_content">
                                    <h4 class="font_16 f_w_700 mb_6">{{__('amazy.Return Policy')}}</h4>
                                    <p class="delivery_text font_14 f_w_400">
                                        {{__('amazy.Easy Return, Quick Refund.')}} <a class="text-nowrap" href="{{url('/return-exchange')}}">{{__('common.see_more')}}.</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if(isModuleActive('MultiVendor'))
                        <div class="amazcart_delivery_wiz mb_30">
                            <div class="amazcart_delivery_wiz_head">
                                <h4 class="font_18 f_w_700 m-0">{{__('amazy.Seller Information')}}</h4>
                            </div>
                            <div class="amazcart_delivery_wiz_body">
                                <h4 class="font_14 f_w_700 mb-0">
                                    <a id="shopLink" href="
                                        @if ($product->seller->slug)
                                            {{route('frontend.seller',$product->seller->slug)}}
                                        @else
                                            {{route('frontend.seller',base64_encode($product->seller->id))}}
                                        @endif
                                    ">
                                        @if($product->seller->role->type == 'seller')
                                            @if (@$product->seller->SellerAccount->seller_shop_display_name)
                                                {{ @$product->seller->SellerAccount->seller_shop_display_name }}
                                            @else
                                                {{$product->seller->first_name .' '.$product->seller->last_name}}
                                            @endif
                                        @else
                                            {{ app('general_setting')->company_name }}
                                        @endif
                                    </a>
                                </h4>
                                @php
                                    $seller_rating_avg = $product->seller->sellerReviews()->where('status',1)->avg('rating');
                                    $seller_score = ($seller_rating_avg * 20);
                                @endphp
                                    <input type="hidden" class="form-control" name="seller_id" id="seller_id" value="{{$product->seller->id}}">
                                    <div class="Information_box d-flex gap-2 flex-wrap ">
                                        <div class="Information_box_left flex-fill">
                                            <div class="single_info_seller d-flex align-items-center gap_15">
                                                <h4 class="font_14 f_w_500 m-0">{{getNumberTranslate($seller_score)}}%</h4>
                                                <p class="font_14 f_w_400 m-0">{{__('amazy.Seller Score')}}</p>
                                            </div>
                                            <div class="single_info_seller d-flex align-items-center gap_15">
                                                <h4 class="font_14 f_w_500 m-0" id="follow_seller_count">{{getNumberTranslate($product->seller->countFollow())}}</h4>
                                                <p class="font_14 f_w_400 m-0">{{__('amazy.Followers')}}</p>
                                            </div>
                                        </div>
                                        <div class="Information_box_right">
                                            @if(auth()->check() && auth()->id()!= $product->seller->id)
                                                @if(auth()->check() && !auth()->user()->follow($product->seller->id))
                                                    <button type="btn" id="follow_seller_btn" class="amaz_primary_btn style3 text-uppercase">{{__('common.follow')}}</button>
                                                @elseif(auth()->check() && auth()->user()->follow($product->seller->id))
                                                    <button type="btn" class="amaz_primary_btn style3 text-uppercase">{{__('amazy.Followed')}}</button>
                                                @endif
                                            @elseif(!auth()->check())
                                                <a href="{{url('/login')}}" class="amaz_primary_btn style3 text-uppercase">{{__('common.follow')}}</a>
                                            @endif
                                        </div>
                                    </div>
                                <div class="seller_performance_box">
                                    <h4 class="font_14 f_w_700 text-uppercase ">{{__('amazy.Seller Performance')}}</h4>
                                    @php
                                        $total_review = $product->seller->sellerReviews->where('status',1)->sum('rating');
                                        $review_count = $product->seller->sellerReviews->where('status',1)->count();
                                        if($total_review != 0){
                                            $review = round($total_review /$review_count,0);
                                        }else{
                                            $review = 1;
                                        }
                                    @endphp

                                        <div class="single_seller_performance d-flex align-items-center gap_10 mb-1">
                                            <img src="{{showImage('frontend/amazy/img/product_details/star.svg')}}" alt="{{@$product->seller->SellerAccount->seller_shop_display_name}}" title="{{@$product->seller->SellerAccount->seller_shop_display_name}}">
                                            <p class="font_14 f_w_400 m-0">{{__('amazy.Order Fulfilment Rate')}}:</p>
                                            <h4 class="font_14 f_w_500 m-0">
                                                @if($review == 1)
                                                {{__('common.very_poor')}}
                                                @elseif($review == 2)
                                                {{__('common.poor')}}
                                                @elseif($review == 3)
                                                {{__('common.neutral')}}
                                                @elseif($review == 4)
                                                {{__('common.satisfactory')}}
                                                @elseif($review == 5)
                                                {{__('common.delightful')}}
                                                @else
                                                {{__('common.very_poor')}}
                                                @endif
                                            </h4>
                                        </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- product_details_wrapper::end  -->
    @if ($product->related_sales->count() > 0)
        <!-- sujjested_prosuct_area::start  -->
        <div class="sujjested_prosuct_area">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="section__title d-flex align-items-center gap-3 mb_30">
                            <h3 class="m-0 flex-fill">{{__('defaultTheme.related_products')}}</h3>
                        </div>
                    </div>
                </div>
                <div class="row">
                    @foreach ($product->related_sales as $key => $related_sale)
                        @foreach ($related_sale->related_seller_products->take(2) as $key => $related_seller_product)
                            <div class="col-xl-3 col-lg-4 col-md-6 d-flex">
                                <div class="product_widget5 mb_30 style5 w-100">
                                    <div class="product_thumb_upper">
                                        @php
                                            if(@$related_seller_product->thum_img != null){
                                                $thumbnail = showImage(@$related_seller_product->thum_img);
                                            }else {
                                                $thumbnail = showImage(@$related_seller_product->product->thumbnail_image_source);
                                            }
                                            $price_qty = getProductDiscountedPrice(@$related_seller_product);
                                            $showData = [
                                                'name' => @$related_seller_product->product_name,
                                                'url' => singleProductURL(@$related_seller_product->seller->slug, @$related_seller_product->slug),
                                                'price' => $price_qty,
                                                'thumbnail' => $thumbnail
                                            ];
                                        @endphp
                                        <a href="{{singleProductURL(@$related_seller_product->seller->slug, $related_seller_product->slug)}}" class="thumb">
                                            @if(app('general_setting')->lazyload == 1)
                                             <img data-src="{{$thumbnail}}" src="{{ showImage(themeDefaultImg()) }}"alt="{{@$related_seller_product->product_name}}" title="{{@$related_seller_product->product_name}}" class="lazyload">
                                            @else
                                             <img  src="{{$thumbnail}}"  alt="{{@$related_seller_product->product_name}}" title="{{@$related_seller_product->product_name}}" >
                                            @endif
                                        </a>
                                        @if(isGuestAddtoCart())
                                            <div class="product_action">
                                                <a href="" class="addToCompareFromThumnail" data-producttype="{{ @$related_seller_product->product->product_type }}" data-seller={{ $related_seller_product->user_id }} data-product-sku={{ @$related_seller_product->skus->first()->id }} data-product-id={{ $related_seller_product->id }}>
                                                    <i class="ti-control-shuffle" title="{{ __('defaultTheme.compare') }}"></i>
                                                </a>
                                                <a href="" class="add_to_wishlist {{$related_seller_product->is_wishlist() == 1?'is_wishlist':''}}" id="wishlistbtn_{{$related_seller_product->id}}" data-product_id="{{$related_seller_product->id}}" data-seller_id="{{$related_seller_product->user_id}}">
                                                    <i class="ti-heart"  title="{{ __('defaultTheme.wishlist') }}"></i>
                                                </a>
                                                <a class="quickView" data-product_id="{{$related_seller_product->id}}" data-type="product">
                                                    <i class="ti-eye" title="{{ __('defaultTheme.quick_view') }}"></i>
                                                </a>
                                            </div>
                                        @endif
                                        <div class="product_badge">
                                            @if(isGuestAddtoCart())
                                                @if($related_seller_product->hasDeal)
                                                    @if($related_seller_product->hasDeal->discount >0)
                                                        <span class="d-flex align-items-center discount">
                                                            @if($related_seller_product->hasDeal->discount_type ==0)
                                                                {{getNumberTranslate($related_seller_product->hasDeal->discount)}} % {{__('common.off')}}
                                                            @else
                                                                {{single_price($related_seller_product->hasDeal->discount)}} {{__('common.off')}}
                                                            @endif
                                                        </span>
                                                    @endif
                                                @else
                                                    @if($related_seller_product->hasDiscount == 'yes')
                                                        @if($related_seller_product->discount >0)
                                                            <span class="d-flex align-items-center discount">
                                                                @if($related_seller_product->discount_type ==0)
                                                                    {{getNumberTranslate($related_seller_product->discount)}} % {{__('common.off')}}
                                                                @else
                                                                    {{single_price($related_seller_product->discount)}} {{__('common.off')}}
                                                                @endif
                                                            </span>
                                                        @endif
                                                    @endif
                                                @endif
                                            @endif
                                            @if(isModuleActive('ClubPoint'))
                                            <span class="d-flex align-items-center point">
                                                <svg width="16" height="14" viewBox="0 0 16 14" fill="none" >
                                                    <path d="M15 7.6087V10.087C15 11.1609 12.4191 12.5652 9.23529 12.5652C6.05153 12.5652 3.47059 11.1609 3.47059 10.087V8.02174M3.71271 8.2357C4.42506 9.18404 6.628 10.0737 9.23529 10.0737C12.4191 10.0737 15 8.74704 15 7.60704C15 6.96683 14.1872 6.26548 12.9115 5.77313M12.5294 3.47826V5.95652C12.5294 7.03044 9.94847 8.43478 6.76471 8.43478C3.58094 8.43478 1 7.03044 1 5.95652V3.47826M6.76471 5.9433C9.94847 5.9433 12.5294 4.61661 12.5294 3.47661C12.5294 2.33578 9.94847 1 6.76471 1C3.58094 1 1 2.33578 1 3.47661C1 4.61661 3.58094 5.9433 6.76471 5.9433Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                {{ @$related_seller_product->product->club_point }}
                                            </span>
                                            @endif
                                            @if(isModuleActive('WholeSale') && @$related_seller_product->skus->first()->wholeSalePrices->count())
                                                <span class="d-flex align-items-center sale">{{__('common.wholesale')}}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="product_star mx-auto">
                                        @php

                                        $reviews = $related_seller_product->reviews->where('status',1)->pluck('rating');
                                        if(count($reviews)>0){
                                            $value = 0;
                                            $rating = 0;
                                            foreach($reviews as $review){
                                                $value += $review;
                                            }
                                            $rating = $value/count($reviews);
                                            $total_review = count($reviews);
                                        }else{
                                            $rating = 0;
                                            $total_review = 0;
                                        }
                                    @endphp
                                        <x-rating :rating="$rating" />
                                    </div>
                                    <div class="product__meta text-center">
                                        <span class="product_banding ">{{ @$related_seller_product->brand->name ?? " " }}</span>
                                        <a href="{{singleProductURL(@$related_seller_product->seller->slug, $related_seller_product->slug)}}">
                                            <h4>@if ($related_seller_product->product_name) {{ textLimit(@$related_seller_product->product_name, 50) }} @else {{ textLimit(@$related_seller_product->product->product_name, 50) }} @endif</h4>
                                        </a>

                                        @if(isGuestAddtoCart())
                                        <div class="product_price d-flex align-items-center justify-content-between flex-wrap">
                                            <a class="amaz_primary_btn add_cart add_to_cart addToCartFromThumnail" data-producttype="{{ @$related_seller_product->product->product_type }}" data-seller={{ $related_seller_product->user_id }} data-product-sku={{ @$related_seller_product->skus->first()->id }}
                                                @if(@$related_seller_product->hasDeal)
                                                    data-base-price={{ selling_price(@$related_seller_product->skus->first()->sell_price,@$related_seller_product->hasDeal->discount_type,@$related_seller_product->hasDeal->discount) }}
                                                @else
                                                    @if(@$related_seller_product->hasDiscount == 'yes')
                                                        data-base-price={{ selling_price(@$related_seller_product->skus->first()->sell_price,@$related_seller_product->discount_type,@$related_seller_product->discount) }}
                                                    @else
                                                        data-base-price={{ @$related_seller_product->skus->first()->sell_price }}
                                                    @endif
                                                @endif
                                                data-shipping-method=0
                                                data-product-id={{ $related_seller_product->id }}
                                                data-stock_manage="{{$related_seller_product->stock_manage}}"
                                                data-stock="{{@$related_seller_product->skus->first()->product_stock}}"
                                                data-min_qty="{{@$related_seller_product->product->minimum_order_qty}}"
                                                data-prod_info="{{ json_encode($showData) }}"
                                                ><svg width="16" height="16" viewBox="0 0 16 16" fill="none" >
                                                    <path d="M0.464844 1.14286C0.464844 0.78782 0.751726 0.5 1.10561 0.5H1.58256C2.39459 0.5 2.88079 1.04771 3.15883 1.55685C3.34414 1.89623 3.47821 2.28987 3.58307 2.64624C3.61147 2.64401 3.64024 2.64286 3.66934 2.64286H14.3464C15.0557 2.64286 15.5679 3.32379 15.3734 4.00811L13.8119 9.50163C13.5241 10.5142 12.6019 11.2124 11.5525 11.2124H6.47073C5.41263 11.2124 4.48508 10.5028 4.20505 9.47909L3.55532 7.10386L2.48004 3.4621L2.47829 3.45572C2.34527 2.96901 2.22042 2.51433 2.03491 2.1746C1.85475 1.84469 1.71115 1.78571 1.58256 1.78571H1.10561C0.751726 1.78571 0.464844 1.49789 0.464844 1.14286ZM4.79882 6.79169L5.44087 9.1388C5.56816 9.60414 5.98978 9.92669 6.47073 9.92669H11.5525C12.0295 9.92669 12.4487 9.60929 12.5795 9.14909L14.0634 3.92857H3.95529L4.78706 6.74583C4.79157 6.76109 4.79548 6.77634 4.79882 6.79169ZM7.72683 13.7857C7.72683 14.7325 6.96184 15.5 6.01812 15.5C5.07443 15.5 4.30942 14.7325 4.30942 13.7857C4.30942 12.8389 5.07443 12.0714 6.01812 12.0714C6.96184 12.0714 7.72683 12.8389 7.72683 13.7857ZM6.4453 13.7857C6.4453 13.5491 6.25405 13.3571 6.01812 13.3571C5.7822 13.3571 5.59095 13.5491 5.59095 13.7857C5.59095 14.0224 5.7822 14.2143 6.01812 14.2143C6.25405 14.2143 6.4453 14.0224 6.4453 13.7857ZM13.7073 13.7857C13.7073 14.7325 12.9423 15.5 11.9986 15.5C11.0549 15.5 10.2899 14.7325 10.2899 13.7857C10.2899 12.8389 11.0549 12.0714 11.9986 12.0714C12.9423 12.0714 13.7073 12.8389 13.7073 13.7857ZM12.4258 13.7857C12.4258 13.5491 12.2345 13.3571 11.9986 13.3571C11.7627 13.3571 11.5714 13.5491 11.5714 13.7857C11.5714 14.0224 11.7627 14.2143 11.9986 14.2143C12.2345 14.2143 12.4258 14.0224 12.4258 13.7857Z" fill="currentColor"/>
                                                </svg>
                                                {{__('defaultTheme.add_to_cart')}}
                                            </a>
                                                <p>
                                                    @if (getProductwitoutDiscountPrice(@$related_seller_product) != single_price(0))
                                                        <del>
                                                            {{getProductwitoutDiscountPrice(@$related_seller_product)}}
                                                        </del>
                                                     @endif
                                                    <strong>
                                                        {{getProductDiscountedPrice(@$related_seller_product)}}
                                                    </strong>
                                                </p>
                                        </div>
                                        @else
                                        <div class="product_price d-flex align-items-center justify-content-between flex-wrap">
                                            <a class="amaz_primary_btn add_cart w-100 " style="text-indent: 0;" href="{{ url('/login') }}">
                                                {{__('defaultTheme.login_to_order')}}
                                            </a>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
        <!-- sujjested_prosuct_area::end  -->
    @endif
    @if(@$product->hasDeal)
        <input type="hidden" id="discount_type" value="{{@$product->hasDeal->discount_type}}">
        <input type="hidden" id="discount" value="{{@$product->hasDeal->discount}}">
    @else
        @if(@$product->hasDiscount == 'yes')
        <input type="hidden" id="discount_type" value="{{$product->discount_type}}">
        <input type="hidden" id="discount" value="{{$product->discount}}">
        @else
        <input type="hidden" id="discount_type" value="{{$product->discount_type}}">
        <input type="hidden" id="discount" value="0">
        @endif
    @endif
    <!--for whole sale price -->
    @if(isModuleActive('WholeSale'))
        <input type="hidden" id="getWholesalePrice" value="@if(@$product->skus->where('status',1)->first()->wholeSalePrices->count()){{ json_encode(@$product->skus->where('status',1)->first()->wholeSalePrices) }} @else 0 @endif">
    @endif
    <input type="hidden" id="isWholeSaleActive" value="{{isModuleActive('WholeSale')}}">
    <input type="hidden" id="isMultiVendorActive" value="{{isModuleActive('MultiVendor')}}">
    @include(theme('components.product_report'),compact('reasons','product'))
@endsection
@push('scripts')
<script src="{{ asset(asset_path('frontend/default/js/zoom.js')) }}"></script>
<script src="{{ asset(asset_path('frontend/default/js/lightbox.js')) }}"></script>
<script>
    (function($){
        "use strict";
        $(document).ready(function(){

            if($(window).outerWidth() > 767 ){
                zoom_enable();
            }

            function zoom_enable(){
                $(".zoom_01").elevateZoom({
                    zoomEnabled: true,
                    zoomWindowHeight:120,
                    zoomWindowWidth:120,
                    zoomLevel:.9
                });
            }

            @if(isModuleActive('CheckPincode'))
                $('#check_pincode_btn').click(function(e){
                    e.preventDefault();
                    var pin_code = $('#check_pincode').val();
                    var seller_id = $('#seller_id').val();

                    $('#pre-loader').show();
                    var data = {pin_code: pin_code,seller_id: seller_id, _token: '{{csrf_token()}}'};
                    $.post("{{route('checkpincode.pin.code.availablity')}}",data,function(response){
                        if(response.data != null){
                            $('.not_serve').addClass('d-none');
                            $('.availablity_show_div').removeClass('d-none');
                            $('#pin_code_area').text(response.data.city+", "+response.data.state);
                            $('#pin_code_delivery').text(response.data.delivery_days+" days");
                            if(response.pinConfig.delivery_days_status==1){
                                $('.pdelivery').removeClass('d-none');
                            }
                            else{
                                $('.pdelivery').addClass('d-none');
                            }
                        }else{
                            $('.availablity_show_div').addClass('d-none');
                            $('.not_serve').removeClass('d-none');
                        }
                        $('#pre-loader').hide();
                    });

                });
            @endif

            if($('#isWholeSaleActive').val() == 1 && $('#getWholesalePrice').val() != 0){
                var getWholesalePrice = JSON.parse($('#getWholesalePrice').val());
                if(getWholesalePrice){
                    appendWholeSaleP();
                    $('.append_w_s_p_tbl').removeClass('d-none');
                }else {
                    $('.append_w_s_p_tbl').addClass('d-none');
                }
            }else{
                var getWholesalePrice = null;
            }
            both_buy_price($('#base_sku_price').val().trim());
            function both_buy_price(product_price){
                let both_buy_price = $('#both_buy_price').val();
                let qty = $('.qty').data('value');
                let total_product_price = parseFloat(product_price) * parseInt(qty);
                let total = currency_format(total_product_price + parseFloat(both_buy_price));
                $('#both_buy_price_show').text(total);
            }
            $(document).on('click', '.page_link', function(event){
                event.preventDefault();
                let page = $(this).attr('href').split('page=')[1];
                fetch_data(page);
            });

            $(document).on('click','.report-product',function(){
                $("#report_modal").modal('show');
            });

            function fetch_data(page){
                $('#pre-loader').show();
                var url = "{{route('frontend.product.reviews.get-data')}}" + '?product_id='+ "{{$product->id}}" +'&page=' + page;
                if(page != 'undefined'){
                    $.ajax({
                        url: url,
                        success:function(data)
                        {
                            $('#Reviews').html(data);
                            $('#pre-loader').hide();
                        }
                    });
                }else{
                    toastr.warning('this is undefined');
                }
            }
            var productType = $('.product_type').val();
            if (productType == 2) {
                '@if (session()->has('item_details') != '')'+
                    '@foreach (session()->get('item_details') as $key => $item)'+
                        '@if ($item['attr_id'] === 1)'+
                            '@foreach ($item['value'] as $k => $value_name)'+
                                $(".colors_{{$k}}").css("background", "{{ $item['code'][$k]}}");
                            '@endforeach'+
                        '@endif'+
                    '@endforeach'+
                '@endif'
            }
            $(document).on('click', '.attr_val_name', function(){
                $(this).parent().parent().find('.attr_value_name').val($(this).attr('data-value')+'-'+$(this).attr('data-value-key'));
                $(this).parent().parent().find('.attr_value_id').val($(this).attr('data-value')+'-'+$(this).attr('data-value-key'));
                if ($(this).attr('color') == "color") {
                    $(this).closest('.color_List').find('.attr_clr').removeClass('selected_btn');
                }
                if ($(this).attr('color') == "not") {
                    $(this).closest('.color_List').find('.not_111').removeClass('selected_btn');
                }
                $(this).addClass('selected_btn');
                get_price_accordint_to_sku();

            });


            $(document).on('click', '.item-slick', function(event){
                var logo = $(this).children().attr("src");
                $('.varintImg').attr("src", logo);
                $('.varintImg').data("zoom-image", logo);
                $('.varintImg').addClass('zoom_01');
                if($(window).outerWidth() > 767 ){
                    zoom_enable();
                }
            });


            $(document).on('click', '.add_to_cart_btn', function(event){
                event.preventDefault();
                var showData = {
                    'name' : "{{ @$product->product_name }}",
                    'url' : "{{singleProductURL(@$product->seller->slug, @$product->slug)}}",
                    'price' : currency_format($('#final_price').val()),
                    'thumbnail' : $('#thumb_image').val()
                };
                addToCart($('#product_sku_id').val(),$('#seller_id').val(),$('#qty').data('value'),$('#base_sku_price').val().trim(),$('#shipping_type').val(),'product',showData);
            });
            $(document).on('click', '#both_buy_btn', function (event){
                event.preventDefault();
                let product_sku_id = $(this).data('sku_id');
                let product_id = $(this).data('product_id');
                let qty = $(this).data('qty');
                let seller_id = $(this).data('seller_id');
                addToCart(product_sku_id, seller_id, qty, $('#both_buy_price').val(), '0', 'product');
                addToCart($('#product_sku_id').val(),$('#seller_id').val(),$('#qty').data('value'),$('#base_sku_price').val().trim(),$('#shipping_type').val(),'product');
            });
            $(document).on('click', '#wishlist_btn', function(event){
                event.preventDefault();
                let product_id = $(this).data('product_id');
                let seller_id = $(this).data('seller_id');
                let type = "product";
                let is_login = $('#login_check').val();
                if(is_login == 1){
                    addToWishlist(product_id, seller_id, type);
                }else{
                    toastr.warning("{{__('defaultTheme.please_login_first')}}","{{__('common.warning')}}");
                }
            });
            $(document).on('click', '#add_to_compare_btn_modify', function(event){
                event.preventDefault();
                let product_sku_class = $(this).data('product_sku_id');
                let product_sku_id = $(product_sku_class).val();
                let product_type = $(this).data('product_type');
                addToCompare(product_sku_id, product_type, 'product');
            });
            $(document).on('click', '.qtyChange', function(event){
                event.preventDefault();
                let value = $(this).data('value');
                qtyChange(value);
            });

            $(document).on('keyup', '#qty', function(event){
                event.preventDefault();
                let qty = $(this).val();
                let available_stock = $('#availability').html();
                let stock_manage_status = $('#stock_manage_status').val();
                let maximum_order_qty = $('#maximum_order_qty').val();
                let minimum_order_qty = $('#minimum_order_qty').val();
                if (stock_manage_status != 0) {
                    if (parseInt(qty) < parseInt(available_stock)) {
                        if (maximum_order_qty != '' && maximum_order_qty != '') {
                            if(parseInt(qty)>=1){
                                if(parseInt(qty) > parseInt(maximum_order_qty) && parseInt(qty) > parseInt(minimum_order_qty)){
                                    toastr.warning('{{__("defaultTheme.maximum_quantity_limit_is")}}'+maximum_order_qty+'.', '{{__("common.warning")}}');
                                }else if(parseInt(qty) < parseInt(maximum_order_qty) && parseInt(qty) < parseInt(minimum_order_qty)){
                                    toastr.warning('{{__("defaultTheme.minimum_quantity_Limit_is")}}'+minimum_order_qty+'.', '{{__("common.warning")}}');
                                }else{
                                    let qty1 = parseInt(qty)
                                    $('#qty').val(numbertrans(qty1));
                                    $('#qty').data('value',qty1);
                                    totalValue(qty1, '#base_price','#total_price', getWholesalePrice);
                                }
                            }else{
                                if (parseInt(qty) == 0) {
                                    toastr.warning('{{__("defaultTheme.minimum_quantity_Limit_is")}}'+minimum_order_qty+'.', '{{__("common.warning")}}');
                                }
                                return false;
                            }
                        }
                        else if(maximum_order_qty != ''){
                            if(parseInt(qty)>=1){
                                if(parseInt(qty) <= parseInt(maximum_order_qty)){
                                let qty1 = parseInt(qty);
                                $('#qty').val(numbertrans(qty1));
                                $('#qty').data('value',qty1);
                                totalValue(qty1, '#base_price','#total_price', getWholesalePrice);
                                }else{
                                    toastr.warning('{{__("defaultTheme.maximum_quantity_limit_is")}}'+maximum_order_qty+'.', '{{__("common.warning")}}');
                                }
                            }else{
                                if (parseInt(qty) == 0) {
                                    toastr.warning('{{__("defaultTheme.minimum_quantity_Limit_is")}}'+minimum_order_qty+'.', '{{__("common.warning")}}');
                                }
                                return false;
                            }
                        }else if(maximum_order_qty != ''){
                            if(parseInt(qty) > parseInt(minimum_order_qty)){
                                if(parseInt(qty)>=1){
                                    let qty1 = parseInt(qty)
                                    $('#qty').val(numbertrans(qty1));
                                    $('#qty').data('value',qty1);
                                    totalValue(qty1, '#base_price','#total_price', getWholesalePrice)
                                }else{
                                    if (parseInt(qty) == 0) {
                                        toastr.warning('{{__("defaultTheme.minimum_quantity_Limit_is")}}'+minimum_order_qty+'.', '{{__("common.warning")}}');
                                    }
                                    return false;
                                }
                            }else{
                                toastr.warning('{{__("defaultTheme.minimum_quantity_Limit_is")}}'+minimum_order_qty+'.', '{{__("common.warning")}}')
                            }
                        }else{
                            if(parseInt(qty)>=1){
                                let qty1 = parseInt(qty);
                                $('#qty').val(numbertrans(qty1));
                                $('#qty').data('value',qty1);
                                totalValue(qty1, '#base_price','#total_price', getWholesalePrice);
                            }else{
                                if (parseInt(qty) == 0) {
                                    toastr.warning('{{__("defaultTheme.minimum_quantity_Limit_is")}}'+minimum_order_qty+'.', '{{__("common.warning")}}');
                                }
                                return false;
                            }
                        }
                    }else{
                        if (parseInt(qty) >= 0) {
                                toastr.error("{{__('defaultTheme.no_more_stock')}}", "{{__('common.error')}}");
                            }
                            return false;
                    }
                }else{
                    if (maximum_order_qty != '' && minimum_order_qty != '') {
                        if(parseInt(qty)>=1){
                            if(parseInt(qty) >= parseInt(maximum_order_qty) && parseInt(qty) >= parseInt(minimum_order_qty)){
                                toastr.warning('{{__("defaultTheme.maximum_quantity_limit_is")}}'+maximum_order_qty+'.', '{{__("common.warning")}}')
                            }else if (parseInt(qty) < parseInt(maximum_order_qty) && parseInt(qty) < parseInt(minimum_order_qty)) {
                                toastr.warning('{{__("defaultTheme.minimum_quantity_Limit_is")}}'+minimum_order_qty+'.', '{{__("common.warning")}}')
                            }else{
                                let qty1 = parseInt(qty);
                                $('#qty').val(numbertrans(qty1));
                                $('#qty').data('value',qty1);
                                totalValue(qty1, '#base_price','#total_price', getWholesalePrice);
                            }
                        }else{
                            if (parseInt(qty) == 0) {
                                toastr.warning('{{__("defaultTheme.minimum_quantity_Limit_is")}}'+minimum_order_qty+'.', '{{__("common.warning")}}');
                            }
                            return false;
                        }

                    }else if(maximum_order_qty != ''){
                        if(parseInt(qty)>=1){
                            if(parseInt(qty) <= parseInt(maximum_order_qty)){
                                let qty1 = parseInt(qty);
                                $('#qty').val(numbertrans(qty1));
                                $('#qty').data('value',qty1);
                                totalValue(qty1, '#base_price','#total_price', getWholesalePrice);
                            }else{
                                toastr.warning('{{__("defaultTheme.maximum_quantity_limit_is")}}'+maximum_order_qty+'.', '{{__("common.warning")}}')
                            }
                        }else{
                            if (parseInt(qty) == 0) {
                                toastr.warning('{{__("defaultTheme.minimum_quantity_Limit_is")}}'+minimum_order_qty+'.', '{{__("common.warning")}}');
                            }
                            return false;
                        }
                    }else if(minimum_order_qty != ''){
                        if(parseInt(qty)>=1){
                            if(parseInt(qty) >= parseInt(minimum_order_qty)){
                                let qty1 = parseInt(qty)
                                $('#qty').val(numbertrans(qty1));
                                $('#qty').data('value',qty1);
                                totalValue(qty1, '#base_price','#total_price', getWholesalePrice)
                            }else{
                                toastr.warning('{{__("defaultTheme.minimum_quantity_Limit_is")}}'+minimum_order_qty+'.', '{{__("common.warning")}}');
                            }
                        }else{
                            if (parseInt(qty) == 0) {
                                toastr.warning('{{__("defaultTheme.minimum_quantity_Limit_is")}}'+minimum_order_qty+'.', '{{__("common.warning")}}');
                            }
                            return false;
                        }
                    }else{
                        if(parseInt(qty)>=1){
                            let qty1 = parseInt(qty);
                            $('#qty').val(numbertrans(qty1));
                            $('#qty').data('value',qty1);
                            totalValue(qty1, '#base_price','#total_price', getWholesalePrice);
                        }else{
                            toastr.warning('{{__("defaultTheme.minimum_quantity_Limit_is")}}'+minimum_order_qty+'.', '{{__("common.warning")}}');
                        }
                    }
                }
            });
            function qtyChange(val){
                $('.cart-qty-minus').prop('disabled',false);
                let available_stock = $('#availability').html();
                let stock_manage_status = $('#stock_manage_status').val();
                let maximum_order_qty = $('#maximum_order_qty').val();
                let minimum_order_qty = $('#minimum_order_qty').val();
                let qty = $('#qty').data('value');
                if (stock_manage_status != 0) {
                    if(val == '+'){
                        if (parseInt(qty) < parseInt(available_stock)) {
                            if(maximum_order_qty != ''){
                                if(parseInt(qty) < parseInt(maximum_order_qty)){
                                let qty1 = parseInt(++qty);
                                $('#qty').val(numbertrans(qty1));
                                $('#qty').data('value',qty1);
                                totalValue(qty1, '#base_price','#total_price', getWholesalePrice);
                                }else{
                                    toastr.warning('{{__("defaultTheme.maximum_quantity_limit_is")}}'+maximum_order_qty+'.', '{{__("common.warning")}}');
                                }
                            }else{
                                let qty1 = parseInt(++qty);
                                $('#qty').val(numbertrans(qty1));
                                $('#qty').data('value',qty1);
                                totalValue(qty1, '#base_price','#total_price', getWholesalePrice);
                            }
                        }else{
                            toastr.error("{{__('defaultTheme.no_more_stock')}}", "{{__('common.error')}}");
                        }
                    }
                    if(val == '-'){
                        if (parseInt(qty) <= parseInt(available_stock)) {
                            if(minimum_order_qty != ''){
                                if(parseInt(qty) > parseInt(minimum_order_qty)){
                                    if(qty>1){
                                        let qty1 = parseInt(--qty)
                                        $('#qty').val(numbertrans(qty1));
                                        $('#qty').data('value',qty1);
                                        totalValue(qty1, '#base_price','#total_price', getWholesalePrice)
                                        $('.cart-qty-minus').prop('disabled',false);
                                    }else{
                                        $('.cart-qty-minus').prop('disabled',true);
                                    }
                                }else{
                                    toastr.warning('{{__("defaultTheme.minimum_quantity_Limit_is")}}'+minimum_order_qty+'.', '{{__("common.warning")}}')
                                }
                            }else{
                                if(parseInt(qty)>1){
                                    let qty1 = parseInt(--qty)
                                    $('#qty').val(numbertrans(qty1));
                                    $('#qty').data('value',qty1);
                                    totalValue(qty1, '#base_price','#total_price', getWholesalePrice)
                                    $('.cart-qty-minus').prop('disabled',false);
                                }else{
                                    $('.cart-qty-minus').prop('disabled',true);
                                }
                            }
                        }else{
                            toastr.error("{{__('defaultTheme.no_more_stock')}}", "{{__('common.error')}}");
                        }
                    }
                }
                else {
                    if(val == '+'){
                        if(maximum_order_qty != ''){
                            if(parseInt(qty) < parseInt(maximum_order_qty)){
                            let qty1 = parseInt(++qty);
                            $('#qty').val(numbertrans(qty1));
                            $('#qty').data('value',qty1);
                            totalValue(qty1, '#base_price','#total_price', getWholesalePrice);
                            }else{
                                toastr.warning('{{__("defaultTheme.maximum_quantity_limit_is")}}'+maximum_order_qty+'.', '{{__("common.warning")}}')
                            }
                        }else{
                            let qty1 = parseInt(++qty);
                            $('#qty').val(numbertrans(qty1));
                            $('#qty').data('value',qty1);
                            totalValue(qty1, '#base_price','#total_price', getWholesalePrice);
                        }
                    }
                    if(val == '-'){
                        if(minimum_order_qty != ''){
                            if(parseInt(qty) > parseInt(minimum_order_qty)){
                                if(qty>1){
                                    let qty1 = parseInt(--qty)
                                    $('#qty').val(numbertrans(qty1));
                                    $('#qty').data('value',qty1);
                                    totalValue(qty1, '#base_price','#total_price', getWholesalePrice)
                                    $('.cart-qty-minus').prop('disabled',false);
                                }else{
                                    $('.cart-qty-minus').prop('disabled',true);
                                }
                            }else{
                                toastr.warning('{{__("defaultTheme.minimum_quantity_Limit_is")}}'+minimum_order_qty+'.', '{{__("common.warning")}}')
                            }
                        }else{
                            if(parseInt(qty)>1){
                                let qty1 = parseInt(--qty)
                                $('#qty').val(numbertrans(qty1));
                                $('#qty').data('value',qty1);
                                totalValue(qty1, '#base_price','#total_price', getWholesalePrice)
                                $('.cart-qty-minus').prop('disabled',false);
                            }else{
                                $('.cart-qty-minus').prop('disabled',true);
                            }
                        }
                    }
                }
            }
            function totalValue(qty, main_price, total_price, getWholesalePrice){
                if($('#isWholeSaleActive').val() == 1 && getWholesalePrice != null){
                    var max_qty='',min_qty='',selling_price='',main_price_vat = 0;
                    for (let i = 0; i < getWholesalePrice.length; ++i) {
                        max_qty = getWholesalePrice[i].max_qty;
                        min_qty = getWholesalePrice[i].min_qty;
                        selling_price = getWholesalePrice[i].sell_price;
                        if ( (min_qty<=qty) && (max_qty>=qty) ){
                            main_price_vat = selling_price;
                            let discount = $('#discount').val();
                            let discount_type = $('#discount_type').val();
                            if (discount_type == 0) {
                                discount = (main_price_vat * discount) / 100;
                            }
                            main_price_vat = (main_price_vat - discount);
                            break;
                        }
                        else if(main_price=='#base_price'){
                            main_price_vat = $('#base_sku_price').val().trim();
                        }
                    }
                    if (main_price != '#base_price') {
                        let discount = $('#discount').val();
                        let discount_type = $('#discount_type').val();
                        if (discount_type == 0) {
                            discount = (main_price_vat * discount) / 100;
                        }
                        var base_sku_price = (main_price_vat - discount);
                    }else{
                        var base_sku_price = main_price_vat;
                    }

                }else {
                    var base_sku_price = $('#base_sku_price').val().trim();
                }
                let value = parseInt(qty) * parseFloat(base_sku_price);
                $(total_price).html(currency_format(value));
                both_buy_price(base_sku_price);
                $('#final_price').val(value);
            }
            function get_price_accordint_to_sku(){
                var value = $("input[name='attr_val_name[]']").map(function(){return $(this).val();}).get();
                var id = $("input[name='attr_val_id[]']").map(function(){return $(this).val();}).get();
                var product_id = $("#product_id").val();
                var user_id = $('#seller_id').val();
                $('#pre-loader').show();
                $.post("{{ route('seller.get_seller_product_sku_wise_price') }}", {_token:'{{ csrf_token() }}', id:id, product_id:product_id, user_id:user_id}, function(response){
                    if (response != 0) {
                        let discount_type = $('#discount_type').val();
                        let discount = $('#discount').val();
                        let qty = $('.qty').data('value');
                        if(typeof response.data.whole_sale_prices != 'undefined'){
                            if(response.data.whole_sale_prices.length > 0){
                                getWholesalePrice = response.data.whole_sale_prices;
                                if(getWholesalePrice){
                                    appendWholeSaleP();
                                    $('.append_w_s_p_tbl').removeClass('d-none');
                                }else {
                                    $('.append_w_s_p_tbl').addClass('d-none');
                                }
                            }
                        }
                        calculatePrice(response.data.selling_price, discount, discount_type, qty);
                        $('#sku_id_li').text(response.data.sku.sku);
                        var color = response.data.sku.sku.split('-');
                        $(".sku_img_div").removeClass('active');
                        $("#"+response.data.sku.sku).addClass('active');
                        var variant_img = response.data.sku.variant_image;
                        if(variant_img){
                        if(variant_img.includes('amazonaws.com')){
                            var image_path = variant_img;
                        }else if(variant_img.includes('digitaloceanspaces.com')){
                            var image_path = variant_img;
                        }else if(variant_img.includes('drive.google.com')){
                            var image_path = variant_img;
                        }else if(variant_img.includes('wasabisys.com')){
                            var image_path = variant_img;
                        }else if(variant_img.includes('backblazeb2.com')){
                            var image_path = variant_img;
                        }else if(variant_img.includes('dropboxusercontent.com')){
                            var image_path = variant_img;
                        }else if(variant_img.includes('storage.googleapis.com')){
                            var image_path = variant_img;
                        }else if(variant_img.includes('contabostorage.com')){
                            var image_path = variant_img;
                        }else if(variant_img.includes('b-cdn.net')){
                            var image_path = variant_img;
                        }else{
                            var image_path="{{asset(asset_path(''))}}/" + variant_img;
                        }
                        $('.varintImg').attr("src", image_path);
                        $('.varintImg').data("zoom-image", image_path);
                        $('.varintImg').addClass('zoom_01');
                        if($(window).outerWidth() > 767 ){
                            zoom_enable();
                        }
                        @if(!empty($public_code))
                            changeTabbyAmount();
                        @endif
                    }
                    $(response.data.product.variantDetails).each(function( key,index ) {
                        if(response.data.product.variantDetails.length > 1){
                            $.each(color, function(i, v) {
                                var isLastElement = i == color.length -1;
                                if (isLastElement) {
                                    $('#color_name').text(index.name +': ' + v);
                                }else{
                                    $('#size_name'+key).text(index.name +': ' + color[key+1]);
                                }
                            });
                            @if(!empty($public_code))
                                changeTabbyAmount();
                            @endif
                        }else{
                            if (index.attr_id == 1) {
                                $('#color_name').text(index.name +': ' + color[1]);
                            }else if (index.attr_id == 2) {
                                $('#size_name').text(index.name +': ' + color[1] + '-'+ color[2]);
                            }else{
                                $('#size_name').text(index.name +': ' + color[1]);
                            }
                        }
                    });
                        $('#product_sku_id').val(response.data.id);
                        if (response.data.product_stock == 0) {
                            $('#availability').html("{{__('defaultTheme.unlimited')}}");
                        }else{
                            $('#availability').html(response.data.product_stock);
                        }
                        if(response.data.product.stock_manage == 1 && parseInt(response.data.product_stock) >= parseInt(response.data.product.product.minimum_order_qty) || response.data.product.stock_manage == 0){
                            @if(isGuestAddtoCart())
                                $('#add_to_cart_div').html(`
                                    <div class="col-md-6">
                                        <button type="button" id="add_to_cart_btn" class="amaz_primary_btn style2 mb_20  add_to_cart text-uppercase add_to_cart_btn flex-fill text-center w-100">{{__('defaultTheme.add_to_cart')}}</button>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="button" id="butItNow" class="amaz_primary_btn3 mb_20  w-100 text-center justify-content-center text-uppercase buy_now_btn" data-id="{{$product->id}}" data-type="product">{{__('common.buy_now')}}</button>
                                    </div>
                                `);
                            @else
                                $('#add_to_cart_div').html(`
                                <div class="col-md-12">
                                                <a href="{{ url('/login') }}" class="amaz_primary_btn w-100">
                                                    {{__('defaultTheme.login_to_order')}}
                                                </a>
                                            </div>
                                `);
                            @endif
                            $('#stock_div').html(`<span class="stoke_badge">{{__('common.in_stock')}}</span>`);
                            if($('#isMultiVendorActive').val() == 1){
                                $('#cart_footer_mobile').html(`
                                    <a href="
                                        @if ($product->seller->slug)
                                            {{route('frontend.seller',$product->seller->slug)}}
                                        @else
                                            {{route('frontend.seller',base64_encode($product->seller->id))}}
                                        @endif
                                    " class="d-flex flex-column justify-content-center product_details_icon">
                                        <i class="ti-save"></i>
                                        <span>{{__('common.store')}}</span>
                                    </a>
                                    <button type="button" class="product_details_button style1 buy_now_btn" data-id="{{$product->id}}" data-type="product">
                                        <span>{{__('common.buy_now')}}</span>
                                    </button>
                                    <button class="product_details_button add_to_cart_btn" type="button">{{__('common.add_to_cart')}}</button>
                                `);
                            }else{
                                if($('#isMultiVendorActive').val() == 1){
                                    $('#cart_footer_mobile').html(`
                                        <a href="
                                            @if ($product->seller->slug)
                                                {{route('frontend.seller',$product->seller->slug)}}
                                            @else
                                                {{route('frontend.seller',base64_encode($product->seller->id))}}
                                            @endif
                                        " class="d-flex flex-column justify-content-center product_details_icon">
                                            <i class="ti-save"></i>
                                            <span>{{__('common.store')}}</span>
                                        </a>
                                        <button type="button" class="product_details_button style1 buy_now_btn" data-id="{{$product->id}}" data-type="product">
                                            <span>{{__('common.buy_now')}}</span>
                                        </button>
                                        <button class="product_details_button add_to_cart_btn" type="button">{{__('common.add_to_cart')}}</button>
                                    `);
                                }else{
                                    $('#cart_footer_mobile').html(`
                                        <button type="button" class="product_details_button style1 buy_now_btn" data-id="{{$product->id}}" data-type="product">
                                            <span>{{__('common.buy_now')}}</span>
                                        </button>
                                        <button class="product_details_button add_to_cart_btn" type="button">{{__('common.add_to_cart')}}</button>
                                    `);
                                }
                            }
                        }
                        else{
                            @if(isGuestAddtoCart())
                                $('#add_to_cart_div').html(`
                                    <div class="col-md-6">
                                        <button type="button" disabled class="amaz_primary_btn style2 mb_20 add_to_cart text-uppercase flex-fill text-center w-100">{{__('defaultTheme.out_of_stock')}}</button>
                                    </div>
                                `);
                            @else
                                $('#add_to_cart_div').html(`
                                    <div class="col-md-12">
                                                    <a href="{{ url('/login') }}" class="amaz_primary_btn w-100">
                                                    {{__('defaultTheme.login_to_order')}}
                                                </a>
                                            </div>
                                `);
                            @endif
                            $('#stock_div').html(`<span class="stokeout_badge">{{__('defaultTheme.out_of_stock')}}</span>`);

                            $('#cart_footer_mobile').html(`
                                <button type="button" class="product_details_button style1" disabled>
                                    <span>{{__('defaultTheme.out_of_stock')}}</span>
                                </button>
                                <button type="button" class="product_details_button" disabled>{{__('defaultTheme.out_of_stock')}}</button>
                            `);
                        }
                    }else {
                        toastr.error("{{__('defaultTheme.no_stock_found_for_this_seller')}}", "{{__('common.error')}}");
                    }
                    $('#pre-loader').hide();
                });
            }

            function calculatePrice(main_price, discount, discount_type, qty){
                var main_price = main_price;
                var discount = discount;
                var discount_type = discount_type;
                var total_price = 0;
                if($('#isWholeSaleActive').val() == 1 && getWholesalePrice != null){
                    var max_qty='',min_qty='',selling_price='';
                    for (let i = 0; i < getWholesalePrice.length; ++i) {
                        max_qty = getWholesalePrice[i].max_qty;
                        min_qty = getWholesalePrice[i].min_qty;
                        selling_price = getWholesalePrice[i].selling_price;

                        if ( (min_qty<=qty) && (max_qty>=qty) ){
                            main_price = selling_price;
                        }
                        else if(max_qty < qty){
                            main_price = selling_price;
                        }
                    }
                    @if(!empty($public_code))
                    changeTabbyAmount();
                    @endif
                }
                if (discount_type == 0) {
                    discount = (main_price * discount) / 100;
                }
                total_price = (main_price - discount);
                $('#total_price').text(currency_format((total_price * qty)));
                both_buy_price((total_price));
                $('#base_sku_price').val(total_price);
                $('#final_price').val(total_price);
            }
            function appendWholeSaleP(){
                $('#append_w_s_p_all').empty();
                $.each(getWholesalePrice, function(index, value) {
                    $('#append_w_s_p_all').append(`
                    <tr class="border-bottom">
                        <td class="text-left">
                            <span>${numbertrans(value.min_qty)}</span>
                        </td>
                        <td class="text-left">
                            <span>${numbertrans(value.max_qty)}</span>
                        </td>
                        <td class="text-left">
                            <span>${currency_format(value.selling_price)}</span>
                        </td>
                    </tr>
                `);
                });
            }
            $(document).on('change', '#select_city', function(event){
                let id = $(this).val();
                let data = {
                    city_id : id,
                    _token : "{{csrf_token()}}",
                    seller_id: "{{$product->seller->id}}"
                }
                $('#pre-loader').show();
                $.post("{{route('frontend.item.get_pickup_by_city')}}",data,function(response){
                    $('#selectPickup').empty();
                    $('#selectPickup').append(
                        `<option selected disabled data-display="Choose pickup location">{{__('amazy.Choose pickup location')}}</option>`
                    );
                    $.each(response, function(index, pickup) {
                        $('#selectPickup').append('<option value="' + pickup.id + '">' + pickup.address + '</option>');
                    });
                    $('#selectPickup').niceSelect('update');
                    $('#pre-loader').hide();
                });
            });
            $(document).on('change', '#selectPickup', function (event){
                getPickupInfo();
            });
            getPickupInfo();
            function getPickupInfo(){
                let pickup_id = $('#selectPickup').val();
                let data = {
                    pickup_id : pickup_id,
                    _token : "{{csrf_token()}}",
                    seller_id: "{{$product->seller->id}}"
                }
                $('#pre-loader').show();
                $.post("{{route('frontend.item.get_pickup_info')}}",data,function(response){
                    if (response.shipping != null) {
                        if(response.shipping.cost == 0){
                            $('#door_delivery').text(`
                                ${trans('amazy.free_shipping_note')} ${response.shipping.shipment_time}.
                            `);
                        }else{
                            $('#door_delivery').text(`
                                ${trans('amazy.shipping_note')} ${currency_format(response.shipping.cost)}. ${trans('amazy.Delivery within')} ${response.shipping.shipment_time}.
                            `);
                        }
                    }
                    if (response.pickup_location != null) {
                        $('#pickup_location').text(`
                            {{__('shipping.delivery_from_pickup_location_always_free_of_cost')}} {{__('common.pickup_address')}}: ${response.pickup_location.address}.
                            {{__('common.country')}}: ${response.pickup_location.country.name} {{__('common.state')}}: ${response.pickup_location.state.name} {{__('common.city')}}: ${response.pickup_location.city.name} {{__('common.postcode')}}: ${response.pickup_location.pin_code}
                        `);
                    }
                    $('#pre-loader').hide();
                });
            }
            $(document).on("click", ".buy_now_btn", function(event){
                event.preventDefault();
                buyNow($('#product_sku_id').val(),$('#seller_id').val(),$('#qty').data('value'),$('#base_sku_price').val().trim(),$('#shipping_type').val(),'product', $('#owner').val());
            });
            $(document).on("click","#follow_seller_btn" ,function(event){
                event.preventDefault();
                let id = $('#seller_id').val();
                let data = {
                    seller_id: id,
                    _token : "{{csrf_token()}}"
                }
                $('#pre-loader').show();
                $(this).prop("disabled",true);
                $.post("{{route('frontend.follow_seller')}}",data,function(response){
                    if(response.message == 'success'){
                        toastr.success("{{__('amazy.Followed Successfully')}}","{{__('common.success')}}");
                        $('#follow_seller_btn').text("{{__('amazy.Followed')}}");
                        $('#follow_seller_count').text(numbertrans(response.result));
                    }
                    else{
                        $(this).prop("disabled",false);
                        toastr.error("{{__('amazy.Not Followed')}}","{{__('common.error')}}");
                    }
                    $('#pre-loader').hide();
                });
            });

            // Showing Review Image Bigger
            $(document).on('click', '.lightboxed', function (event) {
                let selector = $('iframe');
                $.each(selector, function () {
                    let src = $(this).attr('src');
                    let selector = $(this).closest('.lightboxed--frame');
                    let caption = selector.find('.lightboxed--caption').text();
                    $(this).remove();
                    selector.append("<img src='" + src + "' data-caption='" + caption + "'>");
                });
            });

            $(document).on('click','.filter-review',function(){
                let product_id = "{{ $product->id }}";
                let star = $(this).attr('data-review');
                let data = {
                    product_id : product_id,
                    rating : star,
                }
                $.ajax({
                    url: "{{ route('filterReview') }}",
                    method : "get",
                    data : data,
                }).done(function(response){

                        if(response.status == 1){
                            $("#all-reviews").html(response.html);
                            $("#review-pager").remove();
                        }
                });
            });
        });
    })(jQuery);
</script>
<script>


    function zoom_enable_for_variant(){
                $(".zoom_01").elevateZoom({
                    zoomEnabled: true,
                    zoomWindowHeight:120,
                    zoomWindowWidth:120,
                    zoomLevel:.9
                });
    }
    function both_variant_buy_price(product_price){
                let both_buy_price = $('#both_buy_price').val();
                let qty = $('.qty').data('value');
                let total_product_price = parseFloat(product_price) * parseInt(qty);
                let total = currency_format(total_product_price + parseFloat(both_buy_price));
                $('#both_buy_price_show').text(total);
    }
     function calculateVariantProductPrice(main_price, discount, discount_type, qty){
                var main_price = main_price;
                var discount = discount;
                var discount_type = discount_type;
                var total_price = 0;
                if($('#isWholeSaleActive').val() == 1 && getWholesalePrice != null){
                    var max_qty='',min_qty='',selling_price='';
                    for (let i = 0; i < getWholesalePrice.length; ++i) {
                        max_qty = getWholesalePrice[i].max_qty;
                        min_qty = getWholesalePrice[i].min_qty;
                        selling_price = getWholesalePrice[i].selling_price;

                        if ( (min_qty<=qty) && (max_qty>=qty) ){
                            main_price = selling_price;
                        }
                        else if(max_qty < qty){
                            main_price = selling_price;
                        }
                    }
                }
                if (discount_type == 0) {
                    discount = (main_price * discount) / 100;
                }
                total_price = (main_price - discount);
                $('#total_price').text(currency_format((total_price * qty)));
                both_variant_buy_price((total_price));
                $('#base_sku_price').val(total_price);
                $('#final_price').val(total_price);
        }

     function changeProdDetailsByVariantImg(element){
                var color_id = $(element).children("img").data("id");
                var attr_id = $( '.attr_val_name' ).data("value-key");
                $(".sku_img_div").removeClass('active');
                $("#"+color_id).addClass('active');
                var value = $("input[name='attr_val_name[]']").map(function(){return $(this).val();}).get();
                var id = $("input[name='attr_val_id[]']").map(function(){return $(this).val();}).get();

                var product_id = $(element).data("id");
                var user_id = $('#seller_id').val();
                $('#pre-loader').show();

                $.post("{{ route('seller.get_seller_product_variant_wise_price') }}", {_token:'{{ csrf_token() }}', id:id, product_id:product_id, user_id:user_id}, function(response){
                    if (response != 0) {
                        let discount_type = $('#discount_type').val();
                        let discount = $('#discount').val();
                        let qty = $('.qty').data('value');
                        if(typeof response.data.whole_sale_prices != 'undefined'){
                            if(response.data.whole_sale_prices.length > 0){
                                getWholesalePrice = response.data.whole_sale_prices;
                                if(getWholesalePrice){
                                    appendWholeSaleP();
                                    $('.append_w_s_p_tbl').removeClass('d-none');
                                }else {
                                    $('.append_w_s_p_tbl').addClass('d-none');
                                }
                            }
                        }
                        calculateVariantProductPrice(response.data.selling_price, discount, discount_type, qty);
                        $('#sku_id_li').text(response.data.sku.sku);
                        var color = response.data.sku.sku.split('-');
                        $("#"+response.data.sku.sku).addClass('active');
                        var variant_img = response.data.sku.variant_image;
                        var variant_img = variant_img;
                        if(variant_img){
                        if(variant_img.includes('amazonaws.com')){
                            var image_path = variant_img;
                        }else if(variant_img.includes('digitaloceanspaces.com')){
                            var image_path = variant_img;
                        }else if(variant_img.includes('drive.google.com')){
                            var image_path = variant_img;
                        }else if(variant_img.includes('wasabisys.com')){
                            var image_path = variant_img;
                        }else if(variant_img.includes('backblazeb2.com')){
                            var image_path = variant_img;
                        }else if(variant_img.includes('dropboxusercontent.com')){
                            var image_path = variant_img;
                        }else if(variant_img.includes('storage.googleapis.com')){
                            var image_path = variant_img;
                        }else if(variant_img.includes('contabostorage.com')){
                            var image_path = variant_img;
                        }else if(variant_img.includes('b-cdn.net')){
                            var image_path = variant_img;
                        }else{
                            var image_path;
                            if(window.location.origin.includes('localhost')){
                                var strurl = $(location).attr("pathname").split('/');
                                image_path = window.location.origin+'/'+strurl[1]+'/public/' + variant_img;
                            }else{
                                image_path = window.location.origin+'/public/' + variant_img;
                            }
                        }
                        $('.varintImg').attr("src", image_path);
                        $('.varintImg').data("zoom-image", image_path);
                        $('.varintImg').addClass('zoom_01');

                        if($(window).outerWidth() > 767 ){
                            zoom_enable_for_variant();
                        }

                    }

                    var globalSelector=0;
                    var globalColorSelector=0;
                    $(response.data.product.skus).each(function(key,index){
                        if(response.data.product_sku_id==index.product_sku_id){
                            $(index.product_variations).each(function(key2, index2){
                                if(index2.attribute.name=='Shoe Size'){
                                    globalSelector=1;
                                    $('.attr_val_name').removeClass('selected_btn');
                                    $('#attr_val_variant_id_'+index2.attribute_value.id).addClass('selected_btn');
                                }
                                if(index2.attribute.name=='Color'){
                                    if(globalColorSelector==0){
                                        $('.attr_val_name').removeAttr("checked");
                                        globalColorSelector=1;
                                    }
                                    $('.radio_'+index2.attribute_value.id).attr('checked','checked');
                                }
                                if(index2.attribute.name=='Size'){
                                    if(globalSelector==0){
                                        $('.attr_val_name').removeClass('selected_btn');
                                        globalSelector=1;
                                    }
                                    $('#attr_val_variant_id_'+index2.attribute_value.id).addClass('selected_btn');
                                }
                            })
                        }
                    });

                    $(response.data.product.variantDetails).each(function( key,index ) {
                        if(response.data.product.variantDetails.length == 1){
                            $.each(color, function(i, v) {
                                var isLastElement = i == color.length -1;
                                if (isLastElement) {
                                    $('#color_name').text(index.name +': ' + v);
                                }else{
                                    $('#size_name'+key).text(index.name +': ' + color[key+1]);
                                }
                            });
                        }else{
                            if (index.attr_id == 1) {
                                $('#color_name').text(index.name +': ' + color[2]);
                            }else if (index.attr_id == 2) {
                                $('#size_name').text(index.name +': ' + color[1] + '-'+ color[2]);
                            }else{
                                $('#size_name').text(index.name +': ' + color[1]);
                            }
                        }
                    });
                        $('#product_sku_id').val(response.data.id);
                        if (response.data.product_stock == 0) {
                            $('#availability').html("{{__('defaultTheme.unlimited')}}");
                        }else{
                            $('#availability').html(response.data.product_stock);
                        }
                        if(response.data.product.stock_manage == 1 && parseInt(response.data.product_stock) >= parseInt(response.data.product.product.minimum_order_qty) || response.data.product.stock_manage == 0){
                            @if(isGuestAddtoCart())
                                $('#add_to_cart_div').html(`
                                    <div class="col-md-6">
                                        <button type="button" id="add_to_cart_btn" class="amaz_primary_btn style2 mb_20  add_to_cart text-uppercase add_to_cart_btn flex-fill text-center w-100">{{__('defaultTheme.add_to_cart')}}</button>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="button" id="butItNow" class="amaz_primary_btn3 mb_20  w-100 text-center justify-content-center text-uppercase buy_now_btn" data-id="{{$product->id}}" data-type="product">{{__('common.buy_now')}}</button>
                                    </div>
                                `);
                            @else
                                $("#add_to_cart_div").html(`<div class="col-md-12">
                                        <a href="{{ url('/login') }}" class="amaz_primary_btn w-100">
                                        {{__('defaultTheme.login_to_order')}}
                                    </a>
                                </div>`);
                            @endif


                            $('#stock_div').html(`<span class="stoke_badge">{{__('common.in_stock')}}</span>`);
                            if($('#isMultiVendorActive').val() == 1){
                                $('#cart_footer_mobile').html(`
                                    <a href="
                                        @if ($product->seller->slug)
                                            {{route('frontend.seller',$product->seller->slug)}}
                                        @else
                                            {{route('frontend.seller',base64_encode($product->seller->id))}}
                                        @endif
                                    " class="d-flex flex-column justify-content-center product_details_icon">
                                        <i class="ti-save"></i>
                                        <span>{{__('common.store')}}</span>
                                    </a>
                                    <button type="button" class="product_details_button style1 buy_now_btn" data-id="{{$product->id}}" data-type="product">
                                        <span>{{__('common.buy_now')}}</span>
                                    </button>
                                    <button class="product_details_button add_to_cart_btn" type="button">{{__('common.add_to_cart')}}</button>
                                `);
                            }else{
                                if($('#isMultiVendorActive').val() == 1){
                                    $('#cart_footer_mobile').html(`
                                        <a href="
                                            @if ($product->seller->slug)
                                                {{route('frontend.seller',$product->seller->slug)}}
                                            @else
                                                {{route('frontend.seller',base64_encode($product->seller->id))}}
                                            @endif
                                        " class="d-flex flex-column justify-content-center product_details_icon">
                                            <i class="ti-save"></i>
                                            <span>{{__('common.store')}}</span>
                                        </a>
                                        <button type="button" class="product_details_button style1 buy_now_btn" data-id="{{$product->id}}" data-type="product">
                                            <span>{{__('common.buy_now')}}</span>
                                        </button>
                                        <button class="product_details_button add_to_cart_btn" type="button">{{__('common.add_to_cart')}}</button>
                                    `);
                                }else{
                                    $('#cart_footer_mobile').html(`
                                        <button type="button" class="product_details_button style1 buy_now_btn" data-id="{{$product->id}}" data-type="product">
                                            <span>{{__('common.buy_now')}}</span>
                                        </button>
                                        <button class="product_details_button add_to_cart_btn" type="button">{{__('common.add_to_cart')}}</button>
                                    `);
                                }
                            }
                        }
                        else{
                            @if(isGuestAddtoCart())
                                $('#add_to_cart_div').html(`
                                    <div class="col-md-6">
                                        <button type="button" disabled class="amaz_primary_btn style2 mb_20 add_to_cart text-uppercase flex-fill text-center w-100">{{__('defaultTheme.out_of_stock')}}</button>
                                    </div>
                                `);
                            @else
                                $('#add_to_cart_div').html(`
                                    <div class="col-md-12">
                                            <a href="{{ url('/login') }}" class="amaz_primary_btn w-100">
                                            {{__('defaultTheme.login_to_order')}}
                                        </a>
                                    </div>
                                `);
                            @endif

                            $('#stock_div').html(`<span class="stokeout_badge">{{__('defaultTheme.out_of_stock')}}</span>`);

                            $('#cart_footer_mobile').html(`
                                <button type="button" class="product_details_button style1" disabled>
                                    <span>{{__('defaultTheme.out_of_stock')}}</span>
                                </button>
                                <button type="button" class="product_details_button" disabled>{{__('defaultTheme.out_of_stock')}}</button>
                            `);
                        }
                    }else {
                        toastr.error("{{__('defaultTheme.no_stock_found_for_this_seller')}}", "{{__('common.error')}}");
                    }
                    $('#pre-loader').hide();
                });
            }
</script>

@php
    $public_code = null;
    $merchantCode = null;
    $payment = DB::table('payment_methods')->where('method','Tabby')->where('active_status',1)->first();
    if($payment)
    {
        $tabby_gateway = getPaymentGatewayInfo($payment->id);
        if($tabby_gateway){
            $public_code = $tabby_gateway->perameter_1;
            $merchantCode = $tabby_gateway->perameter_5;
        }
    }
@endphp




@if(!empty( $public_code))
    <script src="https://checkout.tabby.ai/tabby-promo.js"></script>
    <script>

        function changeTabbyAmount()
        {
            let amount = $("#total_price").html();
            const price = amount.replace(/[^0-9.]/g, '');
            new TabbyPromo({
                    selector: '#TabbyPromo', // required, content of tabby Promo Snippet will be placed in element with that selector.
                    currency: 'AED', // required, currency of your product. AED|SAR|KWD|BHD|QAR only supported, with no spaces or lowercase.
                    price: price, // required, price or the product. 2 decimals max for AED|SAR|QAR and 3 decimals max for KWD|BHD.
                    installmentsCount: 4, // Optional, for non-standard plans.
                    lang: 'en', // Optional, language of snippet and popups, if the property is not set, then it is based on the attribute 'lang' of your html tag.
                    source: 'product', // Optional, snippet placement; `product` for product page and `cart` for cart page.
                    publicKey: '{{ $public_code }}', // required, store Public Key which identifies your account when communicating with tabby.
                    merchantCode: '{{ $merchantCode }}'
                });
        }

        let amount = $("#total_price").html();
        const price = amount.replace(/[^0-9.]/g, '');
        new TabbyPromo({
            selector: '#TabbyPromo', // required, content of tabby Promo Snippet will be placed in element with that selector.
            currency: 'AED', // required, currency of your product. AED|SAR|KWD|BHD|QAR only supported, with no spaces or lowercase.
            price: price, // required, price or the product. 2 decimals max for AED|SAR|QAR and 3 decimals max for KWD|BHD.
            installmentsCount: 4, // Optional, for non-standard plans.
            lang: 'en', // Optional, language of snippet and popups, if the property is not set, then it is based on the attribute 'lang' of your html tag.
            source: 'product', // Optional, snippet placement; `product` for product page and `cart` for cart page.
            publicKey: '{{ $public_code }}', // required, store Public Key which identifies your account when communicating with tabby.
            merchantCode: 'FONCY'
        });




    </script>
    @endif
@endpush

@include(theme('partials.add_to_cart_script'))
@include(theme('partials.add_to_compare_script'))
