<?php

namespace App\Http\Controllers;

use App\Events\MessegeCreated;
use App\Models\Conversation;
use App\Models\Recipient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Throwable;

class MessagesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($idConversation)
    {
        //
        $user = Auth::user();
        $conversation = $user->conversations()->findOrFail($idConversation);
        return $conversation->messages->paginate();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'message' => ['required', 'string'],
            'conversation_id' => [
                Rule::requiredIf(function () use ($request) {
                    return !$request->input('user_id');
                }),

                'int', 'exists:conversations,id'
            ],
            'user_id' => [
                Rule::requiredIf(function () use ($request) {
                    return !$request->input('conversation_id');
                }),


                'int', 'exists:users,id'
            ]
        ]);
        $user = Auth::user();//User::find(); //Auth::user();
        $conversation_id = $request->post('conversation_id');
        $user_id = $request->post('user_id');
        DB::beginTransaction();
        try {


            if ($conversation_id) {
                $conversation = $user->conversations()->findOrFail($conversation_id);
            }else{
                $conversation=Conversation::where('type','=','peer')
               ->whereHas('participants', function ($builder) use ($user,$user_id){
                    $builder->join('participants as participants2','participants2.conversation_id','=','participants.conversation_id')
                    ->where('participants.user_id','=',$user_id)
                    ->where('participants2.user_id','=',$user->id);
                })->first();  

                if(! $conversation){
                    $conversation=Conversation::create([
                        'user_id'=>$user->id,
                        'type'=>'peer',
                        
                    ]);
                    $conversation->participants()->attach([
                        $user->id=>['joined_at'=>now()],$user_id=>['joined_at'=>now()]
                    ]);

                }
            }

            $message = $conversation->messages()->create([
                'user_id' => $user->id,
                'body' => $request->post('message'),
            ]);
          
            DB::statement('
        INSERT INTO recipients (user_id,message_id)
        SELECT user_id, ? FROM participants WHERE conversation_id= ?', [$message->id, $conversation->id]);

            /***  هنا  بعمل ادخال في جدول المستقبلين لجميع المشاركين والاعضاء في اللي في رقم ال اي دي  تبع المحادثات
             * 
             * علامات الاستفهام هي عبارة عن قيم انت بتبعتهم ل جملة الاستعلام 
             */
            $conversation->update([
'last_messate_id'=>$message->id
            ]);
            DB::commit();
           // $other_user = $message->conversation->participants()->where('user_id', '<>', Auth::id())->first();
           // return    dd($other_user);
           event(new MessegeCreated($message));
            //broadcast(new MessegeCreated($message));  was not work with me
            //same of the top
        } catch (Throwable $ex) {
            DB::rollBack();
            throw $ex;
        }
        return   $message;
    
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //

        Recipient::where([
            'user_id'=>Auth::id(),
            'message_id'=>$id,
        ])->delete();

        return [
         'message'=>'deleted'
        ];
    }
}
