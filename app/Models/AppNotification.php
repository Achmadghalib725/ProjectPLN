<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// AppNotification.php
class AppNotification extends Model {
    protected $guarded = ['id'];
    public function user() { return $this->belongsTo(User::class); }
}
