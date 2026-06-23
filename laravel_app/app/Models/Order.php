<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'customer_name',
        'customer_email',
        'customer_phone',
        'delivery_address',
        'total_price',
        'status',
        'notes',
        'delivered_at',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'delivered_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public static function generateOrderNumber()
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
    }

    public function markAsConfirmed()
    {
        $this->update(['status' => 'confirmed']);
    }

    public function markAsProcessing()
    {
        $this->update(['status' => 'processing']);
    }

    public function markAsShipped()
    {
        $this->update(['status' => 'shipped']);
    }

    public function markAsDelivered()
    {
        $this->update(['status' => 'delivered', 'delivered_at' => now()]);
    }

    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
    }
}
