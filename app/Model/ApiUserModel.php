<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ApiUserModel extends Model
{
    protected $table = "api_user";
    protected $primaryKey = "user_id";
}
