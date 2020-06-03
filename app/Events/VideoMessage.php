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

class VideoMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $chat_id;
    public $user_id;

    public function __construct($message, $chat_id, $user_id)
    {
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


    public function broadcastOn()
    {
        return new PrivateChannel('video.'.$this->chat_id.'.'. $this->user_id);
    }

//    public function broadcastWith()
//    {
//        return [
//            'view' => view('chat', ['message' => '12333222'])->render()
//        ];
//    }
}