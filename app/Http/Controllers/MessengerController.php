<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessengerController  extends Controller
{
    public function index($id = null)
    {
        $user = Auth::user();

        $friends = User::where('id', '<>', $user->id)
            ->orderBy('name')
            ->paginate();
        // return $friends;

        $chats = $user->conversations()->with([
            'lastMessage',
            'participants' => function ($builder) use ($user) {
                $builder->where('id', '<>', $user->id);
            }
        ])->get();
        //return $chats;

        $messages = [];
        if ($id) {
            $conversation = $chats->where('id', $id)->first();
           // return $conversation;
            $messages = $conversation->messages()->with('user')->get();
           
        }
     //  return $messages;
        //
        return view('messenger', [
            'friends' => $friends,
            'chats' => $chats,
            'messages' => $messages,
        ]);
    }
}
