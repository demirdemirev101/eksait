<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class Order extends Model
{
    /**
     * Use the HasFactory trait to enable factory methods for the Order model.
     */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'source',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_address',
        'shipping_city',
        'shipping_postcode',
        'shipping_method',
        'econt_office_code',
        'econt_office_name',
        'econt_office_address',
        'econt_office_is_aps',
        'shipping_country',
        'holiday_delivery_day',
        'status',
        'subtotal',
        'shipping_price',
        'total',
        'payment_method',
        'payment_status',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'stripe_charge_id',
        'stripe_refund_id',
        'refunded_amount',
        'refunded_at',
        'notes'
    ];

    /**
     * Cast specific attributes to appropriate data types for consistent handling in the application.
     */
    protected $casts = [
        'holiday_delivery_day' => 'date',
        'order_confirmation_sent_at' => 'datetime',
        'admin_notification_sent_at' => 'datetime',
        'refunded_at' => 'datetime',
        'refunded_amount' => 'decimal:2',
        'econt_office_is_aps' => 'boolean',
    ];

    /**
     * Define a belongs-to relationship to the User model, indicating that each Order is associated with a single User.
     */
    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Define a has-many relationship to the OrderItem model, indicating that each Order can have multiple associated OrderItems.
     */
    public function items() : HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
    /**
     * Boot method to handle model events. When creating an order, set user_id to null if not provided,
     * indicating a guest checkout. Otherwise it will automatically link the order to the user_id if provided,
     * indicating a registered user checkout.
     */
    public static function booted()
    {
        static::creating(function ($order) {
            if (is_null($order->user_id)) {
                $order->user_id = null;
            }
        });
    }
    /**
     * Define a has-one relationship to the Shipment model, indicating that each Order can have a single associated Shipment.
     */
    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }

    /**
     * Define a has-many relationship to shipments for Filament relation tables.
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}
