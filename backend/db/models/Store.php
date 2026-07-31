<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $table = 'stores';
    public $timestamps = true;
    
    protected $fillable = [
        'parent_id',
        'name',
    ];
}