<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $table = 'wallets';

    protected $fillable = [
        'player_id',
        'game_code',
        'currency_code',
        'amount',
        'label'
        
    ];

    // Relationship with the 'players' table
    public function player()
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

    // Relationship with the 'games' table
    public function game()
    {
        return $this->belongsTo(Game::class, 'game_code');
    }

    // Relationship with the 'currencies' table
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_code');
    }
}
