<?php

namespace Amsgames\LaravelShop\Models;

/**
 * This file is part of LaravelShop,
 * A shop solution for Laravel.
 *
 * @author Alejandro Mostajo
 * @copyright Amsgames, LLC
 * @license MIT
 * @package Amsgames\LaravelShop
 */

use Amsgames\LaravelShop\Contracts\ShopItemInterface;
use Amsgames\LaravelShop\Traits\ShopItemTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use TypiCMS\Modules\Attributes\Models\Attribute;

class ShopItemModel extends Model implements ShopItemInterface
{

    use ShopItemTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table;

    /**
     * Name of the route to generate the item url.
     *
     * @var string
     */
    protected $itemRouteName = '';

    /**
     * Name of the attributes to be included in the route params.
     *
     * @var string
     */
    protected $itemRouteParams = [];

    /**
     * Name of the attributes to be included in the route params.
     *
     * @var string
     */
    protected $fillable = ['user_id', 'session_id', 'cart_id', 'shop_id', 'sku', 'price', 'tax', 'shipping', 'discount', 'currency', 'quantity', 'class', 'reference_id', 'attributes_hash'];

    /**
     * Creates a new instance of the model.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = Config::get('shop.item_table');
    }

    /**
     * Many-to-Many relations with the user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function user()
    {
        return $this->belongsTo(Config::get('auth.providers.users.model'), 'user_id');
    }

    /**
     * One-to-Many relations with the item attributes model.
     */
    public function itemAttributes()
    {
        return $this->hasMany('TypiCMS\Modules\Shop\Models\ItemAttribute');
    }

    /**
     * One-to-One relations with the cart model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cart()
    {
        return $this->belongsTo(Config::get('shop.cart'), 'cart_id');
    }

    /**
     * One-to-One relations with the order model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function order()
    {
        return $this->belongsTo(Config::get('shop.order'), 'order_id');
    }

    /**
     * Returns all selected attributes for the item.
     *
     * @return array
     */
    public function getReadableAttributesAttribute()
    {
        foreach($this->itemAttributes as $attribute) {
            if($attribute->attribute_reference_id) {
                $attr = Attribute::where('attributes.id', $attribute->attribute_reference_id)->with('attributeGroup')->first();
                $attributes[$attr->attributeGroup->value] = $attr->value;
            } else {
                $attributes[$attribute->group_value] = $attribute->attribute_value;
            }
        }
        return $attributes;
    }

    /**
     * Returns the related product to the item.
     *
     * @return string
     */
    public function getProductAttribute()
    {
        $product = call_user_func($this->class . '::find', $this->reference_id);
        return $product->title;
    }
}