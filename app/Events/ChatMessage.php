<?php

namespace App\Events;

use App\Message;
use App\Models\Comment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ChatMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $chat_id;
    public $user_id;

    public function __construct($message, $chat_id, $user_id)
    {
        print_r($message);
        $this->message = $message;
        $this->chat_id = $chat_id;
        $this->user_id = $user_id;
        $this->dontBroadcastToCurrentUser();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */

//    public function broadcastOn()
//    {
//        return new Channel('chat');
//    }

    public function broadcastOn()
    {
        return new PrivateChannel('chat.'.$this->chat_id.'.'. $this->user_id);
    }

//    public function broadcastWith()
//    {
//        return [
//            'view' => view('chat', ['message' => '12333222'])->render()
//        ];
//    }
}