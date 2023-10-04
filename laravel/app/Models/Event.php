<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'events';
    
    protected $fillable = [
        'player_id',
        'game_code',
        'name',
        'value',
        
    ];

    // Relationship dengan tabel players
    public function player()
    {
        return $this->belongsTo(Player::class, 'player_id', 'id');
    }

    // Relationship dengan tabel game
    public function game()
    {
        return $this->belongsTo(Game::class, 'game_code', 'code');
    }
}
