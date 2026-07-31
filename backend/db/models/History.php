<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $table = 'histories';
    public $timestamps = true;
    
    protected $fillable = [
        'customer_user_id',
        'store_id',
        'score',
        'time',
    ];
}