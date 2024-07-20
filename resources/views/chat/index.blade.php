<!-- resources/views/chat/index.blade.php -->

@extends('adminlte::page')

@section('title', 'Chat')

@section('content_header')
    <h1>Chat</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Direct Chat</h3>
        </div>
        <div class="card-body">
            <div class="direct-chat-messages" style="height: 400px; overflow-y: scroll;">
                @foreach($messages as $message)
                    <div class="direct-chat-msg {{ $message->user_id == Auth::id() ? 'right' : '' }}">
                        <div class="direct-chat-infos clearfix">
                            <span class="direct-chat-name float-left">{{ $message->user->name }}</span>
                            <span class="direct-chat-timestamp float-right">{{ $message->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="direct-chat-text">
                            {{ $message->message }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="card-footer">
            <form action="{{ route('chat.store') }}" method="POST">
                @csrf
                <div class="input-group">
                    <input type="text" name="message" placeholder="Type Message ..." class="form-control">
                    <span class="input-group-append">
                        <button type="submit" class="btn btn-primary">Send</button>
                    </span>
                </div>
            </form>
        </div>
    </div>
@stop
