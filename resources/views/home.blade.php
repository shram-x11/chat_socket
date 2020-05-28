@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="row justify-content-center">
            <div class="mb-2 col-md-3">
                @foreach($users as $user)
                    <div>
                        <a href="/chat/{{$user->id}}">{{$user->name}}</a>
                    </div>

                @endforeach
            </div>
            <div class="mb-2 col-md-3">
            </div>
        </div>
    </div>
@endsection
