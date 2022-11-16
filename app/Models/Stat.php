<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
    use HasFactory;
    protected $fillable = ['value','name','player_id'];

    public function player(){
        return $this->belongsTo(Player::class);
    }
}
