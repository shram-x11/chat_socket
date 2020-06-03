<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use App\Message;
use App\User;
use Illuminate\Support\Facades\Auth;
use App\Events\ChatMessage;
use App\Events\VideoMessage;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function index()
    {
        $users = User::all();

//        $receiver = User::find(1);
//
//        $messageData = [
//            'content' => 'Hello all this is just a test', // the content of the message
//            'to_id' => $receiver->getKey(), // Who should receive the message
//        ];
//
//        list($message, $user) = User::createFromRequest($messageData);
//
//        $sender = User::find(2);
//
//        $sent = $sender->writes($message)
//            ->to($user)
//            ->send();
//
//        $userA = User::find(2);
//        $userB = User::find(1);
//
//
//        $messages = $userA->sent()->to($userB)->get();


        return view('home', ['users' => $users]);
    }

    public function chat($id)
    {
        $userA = User::find(Auth::user()->id);
        $userB = User::find($id);
        $messagesA = $userA->sent()->to($userB)->get();
        $messagesB = $userA->received()->from($userB)->get();
        $messages = $messagesA->merge($messagesB)->sortBy('id');

        return view('chat', ['messages' => $messages, 'user_id' => Auth::user()->id, 'id' => $id]);


    }

    public function chatGet($id)
    {
        $userA = User::find(Auth::user()->id);
        $userB = User::find($id);
        $messagesA = $userA->sent()->to($userB)->get();
        $messagesB = $userA->received()->from($userB)->get();
        $messages = $messagesA->merge($messagesB)->sortBy('id');

        return $messages;


    }

    public function chatSend(Request $request, $chat_id)
    {
        $receiver = User::find($chat_id);

        if ($request->file()) {
            $path = $request->file('file')->storeAs('public', $request->file('file')->getClientOriginalName());
            $messageData = [
                'content' => $request->file('file')->getClientOriginalName(), // the content of the message
                'to_id' => $receiver->getKey(), // Who should receive the message
                'type' => 'file'
            ];
        } else {
            $messageData = [
                'content' => $request->message, // the content of the message
                'to_id' => $receiver->getKey(), // Who should receive the message
                'type' => 'text'
            ];
        }




        list($message, $user) = User::createFromRequest($messageData);


        $sender = User::find(Auth::user()->id);


        if ($request->video) {
            event(new VideoMessage([$request->video, $request->action], $chat_id, $sender->id));
        } else {
            $sent = $sender->writes($message)
                ->to($user)
                ->send();
            event(new ChatMessage($message, $chat_id, $sender->id));
        }
        return response()->json($message);


    }
}