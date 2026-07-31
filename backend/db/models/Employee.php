<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employees';
    public $timestamps = true;
    
    protected $fillable = [
        'store_id',
        'name',
        'password',
        'role',
    ];

    public static function tryLogin($name, $password) :?Employee {
        $employee = Employee::where('name', $name)->first();
        if ($employee && password_verify($password, $employee->password)) {
            return $employee;
        }
        return null;
    }
}