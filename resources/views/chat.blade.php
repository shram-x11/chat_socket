@extends('layouts.app')
<script src="//{{ Request::getHost() }}:6001/socket.io/socket.io.js"></script>
@section('content')
    <chat-box chat_id={{ $id }} user_id={{ $user_id }}></chat-box>

    {{--<div class="container">--}}
        {{--@foreach($messages as $message)--}}
            {{--<div style="width: 100%; overflow: auto">--}}
                {{--<div style="background: #ccc; @if($user_id == $message->from_id) float: right @endif"--}}
                     {{--class="mb-2 col-md-3">--}}
                    {{--{{$message->content}}--}}
                {{--</div>--}}
            {{--</div>--}}

        {{--@endforeach--}}
    {{--</div>--}}
    {{--<video id="player" width="300"></video>--}}
    {{--<div style="position: fixed; bottom: 10px;" class="input-group mb-3">--}}
        {{--<input type="text" class="form-control" placeholder="Введите текст сообщения" aria-label="Recipient's username"--}}
               {{--aria-describedby="button-addon2">--}}
        {{--<div class="input-group-append">--}}
            {{--<button class="btn btn-outline-secondary" type="button" id="button-addon2">Отправить</button>--}}
        {{--</div>--}}
    {{--</div>--}}
@endsection


{{--<script src="https://cdn.jsdelivr.net/npm/p2p-media-loader-core@latest/build/p2p-media-loader-core.min.js"></script>--}}
{{--<script src="https://cdn.jsdelivr.net/npm/p2p-media-loader-hlsjs@latest/build/p2p-media-loader-hlsjs.min.js"></script>--}}
{{--<script type="text/javascript"--}}
        {{--src="https://cdn.jsdelivr.net/npm/clappr@latest/dist/clappr.min.js">--}}
{{--</script>--}}
