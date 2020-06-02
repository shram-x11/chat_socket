<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


Broadcast::routes(['middleware' => ['web', 'auth']]);
Broadcast::channel('chat.{chat_id}.{user_id}', function () {
    return true;
});
Broadcast::channel('video.{chat_id}.{user_id}', function () {
    return true;
});