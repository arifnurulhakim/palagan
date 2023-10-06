<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    use HasFactory;

    protected $table = 'user_activity'; // Nama tabel sesuai dengan yang Anda berikan

    protected $fillable = [
        'id_user',
        'activity_key',
        'activity_value',
        'submit_time',
    ];

    protected $dates = [
        'submit_time',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
