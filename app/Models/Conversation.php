<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'lable', 'last_messate_id'];
    // Now we will make relations to conversation

    // the participats in the conversation
    public function participants()
    {
        return $this->belongsToMany(User::class, 'participants')->withPivot([
            'joined_at', 'role'
        ]);
        // to return extra information with participants relationsship

    }

    // the massage

    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id', 'id');
        // to order list the message from the new to old
    }


    // the user who created the conversation
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


    // the last Message
    public function lastMessage()
    {
        return $this->belongsTo(Message::class, 'last_messate_id', 'id')->withDefault();
    }
}
