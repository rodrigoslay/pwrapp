<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\Message;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */

    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

         // Compartir los mensajes con todas las vistas
         View::composer('chat.right-sidebar', function ($view) {
            $messages = Message::with('user')->orderBy('created_at', 'desc')->take(100)->get()->reverse();
            $view->with('messages', $messages);
        });
    }
}
