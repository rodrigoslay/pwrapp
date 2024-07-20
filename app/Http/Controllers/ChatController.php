<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:255',
        ]);

        $message = Message::create([
            'user_id' => Auth::id(),
            'message' => $request->message,
        ]);

        // Cargar el usuario junto con el mensaje
        $message->load('user');

        // Añadir la URL de la imagen del usuario
        $message->user->adminlte_image = $message->user->adminlte_image();

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'status' => 'Mensaje Enviado!',
            'message' => $message,
        ]);
    }
}
