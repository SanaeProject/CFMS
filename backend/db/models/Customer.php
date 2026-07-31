<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';
    public $timestamps = true;

    protected $fillable = [
        'paid',
        'disabled',
    ];

    public static function booted()
    {
        static::creating(function (Customer $customer) {
            if (empty($customer->user_id)) {
                $customer->user_id = \Illuminate\Support\Facades\DB::raw('UUID_SHORT()');
            }
        });
    }

    public function pay(): void
    {
        $this->paid = true;
        $this->save();
    }

    public function disable(): void
    {
        $this->disabled = true;
        $this->save();
    }

    public function canPlayGame(): bool
    {
        return $this->paid && !$this->disabled;
    }
}