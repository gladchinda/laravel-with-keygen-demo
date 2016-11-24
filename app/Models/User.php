<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';

    public $timestamps = false;

    public $incrementing = false;

    public function setEmailAttribute($email)
    {
        // Ensure valid email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception("Invalid email address.");
        }

        // Ensure email does not exist
        elseif (static::whereEmail($email)->count() > 0) {
            throw new \Exception("Email already exists.");
        }

        $this->attributes['email'] = $email;
    }
}
