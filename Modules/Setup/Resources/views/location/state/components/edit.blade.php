
<div class="main-title">
    <h3 class="mb-20">
        {{__('common.edit')}} {{__('common.state')}}</h3>
</div>



<form enctype="multipart/form-data" id="edit_form">
    <div class="white-box mb-5">
        <div class="row">
            <input type="hidden" name="id" value="{{$state->id}}">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="primary_input mb-25">
                    <label class="primary_input_label"
                        for="name">{{ __('common.name') }} <span class="text-danger">*</span></label>
                    <input name="name" class="primary_input_field name"
                        id="name" placeholder="{{ __('common.name') }}" value="{{$state->name}}"
                        type="text">
                    <span class="text-danger"  id="error_name"></span>
                </div>
            </div>
    
            <div class="col-lg-12">
                <div class="primary_input mb-25">
                    <label class="primary_input_label" for="">{{ __('common.country') }} {{ __('common.list') }}</label>
                    <select name="country" id="country" class="primary_select mb-15">
                        <option value="" disabled selected>{{__('common.select_one')}}</option>
                        @foreach ($countries as $key => $country)
                            <option {{$state->country_id == $country->id?'selected':''}} value="{{ $country->id }}">{{ @$country->name }}</option>
                        @endforeach

                    </select>
                    <span class="text-danger"  id="error_country"></span>
                </div>
            </div>
    
            <div class="col-xl-12">
                <div class="primary_input">
                    <label class="primary_input_label" for="">{{ __('common.status') }}</label>
                    <ul id="theme_nav" class="permission_list sms_list ">
                        <li>
                            <label data-id="bg_option"
                                   class="primary_checkbox d-flex mr-12">
                                <input name="status" id="status_active" value="1" {{$state->status == 1?'checked':''}} class="active" type="radio">
                                <span class="checkmark"></span>
                            </label>
                            <p>{{ __('common.active') }}</p>
                        </li>
                        <li>
                            <label data-id="color_option"
                                   class="primary_checkbox d-flex mr-12">
                                <input name="status" value="0" id="status_inactive" {{$state->status == 0?'checked':''}} class="de_active"
                                       type="radio">
                                <span class="checkmark"></span>
                            </label>
                            <p>{{ __('common.inactive') }}</p>
                        </li>
                    </ul>
                    <span class="text-danger" id="status_error"></span>
                </div>
            </div>
    
            <div class="col-lg-12 text-center">
                <div class="d-flex justify-content-center pt_20">
                    <button type="submit" class="primary-btn semi_large2 fix-gr-bg"><i
                            class="ti-check"></i>
                            {{ __('common.update') }}
                    </button>
                </div>
            </div>
    
        </div>
    </div>
</form>
