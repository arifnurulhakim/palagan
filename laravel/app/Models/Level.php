<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $table = 'levels';

    protected $fillable = [
        'player_id',
        'game_code',
        'level',
     
    ];

    public function player()
    {
        return $this->belongsTo(Player::class, 'player_id', 'id');
    }

    public function game()
    {
        return $this->belongsTo(Game::class, 'game_code', 'code');
    }
}
