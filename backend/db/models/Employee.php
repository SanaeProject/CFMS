<?php
namespace Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employees';
    public $timestamps = true;
    
    protected $fillable = [
        'store_id',
        'name',
        'role',
    ];

    protected $hidden = [
        'password',
    ];

    public function setPassword($password) {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }
    public static function tryLogin($name, $password) :?Employee {
        $employee = Employee::where('name', $name)->first();
        if ($employee && password_verify($password, $employee->password)) {
            return $employee;
        }
        return null;
    }
}