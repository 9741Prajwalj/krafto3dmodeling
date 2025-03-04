<?php

namespace Modules\Seller\Entities;

use App\Models\User;
use App\Models\Wishlist;
use App\Models\UsedMedia;
use App\Models\OrderProductDetail;
use Modules\Product\Entities\Brand;
use Illuminate\Support\Facades\Cache;
use Modules\Product\Entities\Product;
use Modules\Menu\Entities\MenuElement;
use Modules\Product\Entities\Category;
use Illuminate\Database\Eloquent\Model;
use Modules\Product\Entities\ProductSku;
use Spatie\Translatable\HasTranslations;
use Modules\Review\Entities\ProductReview;
use Modules\Product\Entities\ProductUpSale;
use Modules\Product\Entities\CategoryProduct;
use Modules\Product\Entities\ProductCrossSale;
use Modules\Marketing\Entities\FlashDealProduct;
use Modules\Product\Entities\ProductRelatedSale;
use Modules\INTShipping\Entities\ShippingProfile;
use Modules\Appearance\Entities\HeaderSliderPanel;
use Modules\Marketing\Entities\NewUserZoneProduct;
use Modules\Appearance\Entities\HeaderProductPanel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\FrontendCMS\Entities\HomepageCustomProduct;

class SellerProduct extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;
    use \Znck\Eloquent\Traits\BelongsToThrough;
    use HasFactory, HasTranslations;
    protected $guarded = ['id'];
    public $translatable = ['product_name', 'subtitle_1', 'subtitle_2'];
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'product_id' => 'integer',
        'tax' => 'double',
        'tax_type' => 'string',
        'discount' => 'double',
        "discount_type" => "integer",
        'discount_start_date' => 'date',
        'discount_end_date' => 'date',
        'product_name' => 'string',
        'slug' => 'string',
        'thum_img' => 'string',
        'status' => 'integer',
        'stock_manage' => 'integer',
        'is_approved' => 'integer',
        'min_sell_price' => 'double',
        'max_sell_price' => 'double',
        'total_sale' => 'integer',
        'avg_rating' => 'integer',
        'recent_view' => 'datetime',
        'subtitle_1' => 'string',
        'subtitle_2' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'MaxSellingPrice' => 'double',
        'rating' => 'integer',
        'hasDiscount' => 'string',
        'ProductType' => 'string',
    ];
    protected $with = ['flashDeal'];
    protected $appends = ['variantDetails', 'MaxSellingPrice', 'hasDeal', 'rating', 'hasDiscount', 'ProductType'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

    }

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->slug = $model->createSlug($model->slug);
        });
        self::created(function ($model) {
            Cache::forget('MegaMenu');
            Cache::forget('HeaderSection');
        });
        self::updating(function ($model) {
            $model->slug = $model->createSlug($model->slug, $model->id);
        });
        self::updated(function ($model) {
            Cache::forget('MegaMenu');
            Cache::forget('HeaderSection');
        });
        self::deleted(function ($model) {
            Cache::forget('MegaMenu');
            Cache::forget('HeaderSection');
        });
    }

    private function createSlug($name, $model = null)
    {
        $str_slug = strtolower(str_replace(" ", "-", $name));
        return $this->abalivaslug($str_slug, 0, $model);
    }
    private function abalivaslug($slug, $count = 0, $model = null)
    {
        if ($count) {
            $newslug = $slug . '-' . $count;
        } else {
            $newslug = $slug;
        }
        if (static::whereSlug($newslug)->where('id', '!=', $model)->first()) {
            return $this->abalivaslug($slug, $count + 1, $model);
        }
        return $newslug;
    }
    public function productSKU()
    {
        return $this->belongsTo(ProductSku::class, "product_sku", "id");
    }

    public function skus()
    {
        return $this->hasMany(SellerProductSKU::class, 'product_id', 'id');
    }

    public function related_sales()
    {
        return $this->hasMany(ProductRelatedSale::class, 'product_id', 'product_id')->with('product.gstGroup');
    }

    public function cross_sales()
    {
        return $this->hasMany(ProductCrossSale::class, 'product_id', 'product_id');
    }

    public function up_sales()
    {
        return $this->hasMany(ProductUpSale::class, 'product_id', 'product_id');
    }

    public function scopeHasPrice($query)
    {
        return $query->whereHas('skus')->orderBy('skus.selling_price', 'desc');
    }

    public function getMaxSellingPriceAttribute()
    {
        return $this->attributes['max_sell_price'];
    }

    public function product()
    {
        return $this->belongsTo(Product::class, "product_id", "id");
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function getVariantDetailsAttribute()
    {
        $product = $this->load('skus', 'skus.product_variations');
        $attr_value = array();
        foreach ($product->skus->where('status', 1) as $key => $sku) {
            foreach ($sku->product_variations as $k => $variation) {
                $blank = new \stdClass();
                $blank->value = [];
                $blank->code = [];
                $blank->attr_val_id = [];
                if (sizeof(array_filter($attr_value, function ($object) use ($variation) {
                    return $object->name == $variation->attribute->name;
                }))) {
                    foreach ($attr_value as $key => $object) {
                        $val_name = @$variation->attribute_value->color ? @$variation->attribute_value->color->name : @$variation->attribute_value->value;
                        $code = $variation->attribute_value->value;
                        $val_id = @$variation->attribute_value->color ? @$variation->attribute_value->color->attribute_value_id : @$variation->attribute_value->id;
                        if ($variation->attribute->name == $object->name) {
                            if (!in_array($val_name, $object->value, true)) {
                                array_push($object->value, $val_name);
                                array_push($object->code, $code);
                                array_push($object->attr_val_id, (int) $val_id);
                            }
                        }
                    }
                } else {
                    $val_name = @$variation->attribute_value->color ? @$variation->attribute_value->color->name : @$variation->attribute_value->value;
                    $val_id = @$variation->attribute_value->color ? @$variation->attribute_value->color->attribute_value_id : @$variation->attribute_value->id;
                    $code = $variation->attribute_value->value;
                    $blank->name = $variation->attribute->name;
                    $blank->attr_id = $variation->attribute->id;
                    array_push($attr_value, $blank);
                    array_push($blank->value, $val_name);
                    array_push($blank->code, $code);
                    array_push($blank->attr_val_id, $val_id);
                }
            }
        }
        return $attr_value;
    }
    public function flashDealProducts()
    {
        return $this->hasMany(FlashDealProduct::class, 'seller_product_id', 'id');
    }
    public function newUserZoneProducts()
    {
        return $this->hasMany(NewUserZoneProduct::class, 'seller_product_id', 'id');
    }
    public function reviews()
    {
        return $this->hasMany(ProductReview::class, 'product_id', 'id')->where('type', 'product')->where('status', 1);
    }
    public function getActiveReviewsWithPaginateAttribute()
    {
        return ProductReview::where('product_id', $this->id)->where('status', 1)->latest()->paginate(10);
    }

    public function scopeActiveSeller($query)
    {
        $query = $query->where('seller_products.user_id', 1)->whereHas('product', function ($q) {
            return $q->where('products.status', 1);
        })->where('seller_products.status', 1);
        if (isModuleActive('MultiVendor')) {
            $query = $query->orWhereHas('seller', function ($q) {
                return $q->whereHas('SellerAccount', function ($q1) {
                    return $q1->where('holiday_mode', 0)->orWhere('holiday_date', '!=', date('Y-m-d'))->orWhere(function ($q2) {
                        return $q2->where('holiday_date_start', '>', date('Y-m-d'))->where('holiday_date_end', '<', date('Y-m-d'))
                            ->orWhere('holiday_date_start', '>', date('Y-m-d'))->orWhere('holiday_date_end', '<', date('Y-m-d'));
                    });
                })
                    ->whereHas('SellerSubscriptions', function ($q5) {
                        return $q5->where('expiry_date', '>', date('Y-m-d'))->whereHas('user.SellerAccount', function ($q6) {
                            return $q6->where('seller_commission_id', 3);
                        });
                    })->where('is_active', 1)->orWhereHas('SellerAccount', function ($q7) {
                        return $q7->where('seller_commission_id', '!=', 3)
                            ->where('holiday_mode', 0)->orWhere('holiday_date', '!=', date('Y-m-d'))->orWhere(function ($q2) {
                                return $q2->where('holiday_date_start', '>', date('Y-m-d'))->where('holiday_date_end', '<', date('Y-m-d'))
                                    ->orWhere('holiday_date_start', '>', date('Y-m-d'))->orWhere('holiday_date_end', '<', date('Y-m-d'));
                            });
                    })->where('is_active', 1);
            })->where('seller_products.status', 1);
        }
        return $query;
    }

    public function getRatingAttribute()
    {
        $reviews = $this->reviews;
        if (count($reviews) > 0) {
            $value = 0;
            $rating = 0;
            foreach ($reviews as $review) {
                $value += $review->rating;
            }
            $rating = $value / count($reviews);
            $total_review = count($reviews);
        } else {
            $rating = 0;
            $total_review = 0;
        }
        return $rating;
    }

    public function getHasDealAttribute()
    {
        if (!$this->relationLoaded('flashDeal')) {
            return 0;
        }
        $dealproduct = $this->flashDeal;
        if (!$dealproduct) {
            return 0;
        }
        $start_date = date('Y/m/d', strtotime($dealproduct->flashDeal->start_date));
        $end_date = date('Y/m/d', strtotime($dealproduct->flashDeal->end_date));
        $current_date = date('Y/m/d');
        if ($start_date <= $current_date && $end_date >= $current_date) {
            return $dealproduct;
        }
        return 0;
    }

    public function flashDeal()
    {
        return $this->hasOne(FlashDealProduct::class, 'seller_product_id', 'id')->with('flashDeal')->whereHas('flashDeal', function ($q) {
            return $q->where('status', 1);
        });
    }
    public function gethasDiscountAttribute()
    {
        if ($this->discount_start_date != null && $this->discount_end_date != null) {
            $start_date = date('m/d/Y', strtotime($this->discount_start_date));
            $end_date = date('m/d/Y', strtotime($this->discount_end_date));
            if ($this->discount > 0) {
                if ($start_date < date('m/d/Y') && $end_date > date('m/d/Y')) {
                    return 'yes';
                } else {
                    return 'no';
                }
            } else {
                return 'no';
            }
        } else {
            if ($this->discount > 0) {
                return 'yes';
            } else {
                return 'no';
            }
        }
    }
    public function is_wishlist()
    {
        if ($this->wishList) {
            return 1;
        } else {
            return 0;
        }
    }
    public function wishList()
    {
        $wishlist = $this->hasOne(Wishlist::class, 'seller_product_id', 'id')->where('type', 'product');
        $user_id = 0;
        if (auth()->check()) {
            $user_id = auth()->id();
        }
        $wishlist = $wishlist->where('user_id', $user_id);
        return $wishlist;
    }

    public function scopeTotalProducts($query)
    {
        $seller_id = getParentSellerId();
        return $query->with('product', 'skus')->where('user_id', $seller_id)->get()->count();
    }

    public function scopeTopSaleProducts($query)
    {
        $seller_id = getParentSellerId();
        return $query->with('product', 'skus')->where('user_id', $seller_id)->orderBy('total_sale', 'desc')->take(10)->get();
    }

    public function scopeLatestUploadedProducts($query)
    {
        $seller_id = getParentSellerId();
        return $query->with('product', 'skus')->where('user_id', $seller_id)->latest()->take(10)->get();
    }

    public function getMenuElementsAttribute()
    {
        return MenuElement::where('type', 'product')->where('element_id', $this->id)->get();
    }

    public function headerProductPanel()
    {
        return $this->belongsTo(HeaderProductPanel::class, 'id', 'product_id');
    }

    public function getSildersAttribute()
    {
        return HeaderSliderPanel::where('data_type', 'product')->where('data_id', $this->id)->get();
    }

    public function homepageCustomProducts()
    {
        return $this->hasMany(HomepageCustomProduct::class, 'seller_product_id', 'id');
    }

    public function getOrdersAttribute()
    {
        return OrderProductDetail::where('type', 'product')->whereHas('seller_product_sku', function ($query) {
            $query->whereHas('product', function ($q) {
                $q->where('id', $this->id);
            });
        })->get();
    }
    public function getProductTypeAttribute(){
        return 'product';
    }
    public function thumb_image_media(){
        return $this->morphOne(UsedMedia::class, 'usable')->where('used_for', 'thumb_image');
    }
    public function brand(){
        return $this->belongsToThrough(Brand::class, Product::class);
    }
    public function categories(){
        return $this->hasManyDeep(Category::class,
            [
                CategoryProduct::class,
                Product::class
            ],
            [
                'product_id', // Foreign key on the "users" table.
                'id',     // Foreign key on the "comments" table.
                'id'    // Foreign key on the "posts" table.
            ],
            [
                'product_id',               // Local key on "tool_groups" table
                'category_id',          // Local key on pivot table
                'id'                // Local key on "tools" table
            ]
        );
    }

    public function shippingProfiles()
    {
        return $this->belongsToMany(ShippingProfile::class)->withPivot('shipping_profile_id', 'seller_product_id');
    }

    public function getThumImgAttribute($value): string
    {
        return   (string) $value;
    }
}
