<aside class="control-sidebar control-sidebar-dark" style="height: 650px;">
    <div class="p-3">
        <h5>Chat</h5>
        <div id="chat-box" class="chat-box" style="height: 600px; overflow-y: auto; padding: 10px; box-sizing: border-box;">
            @foreach ($messages as $message)
                <div class="direct-chat-msg @if($message->user_id === Auth::id()) right @endif">
                    <div class="direct-chat-infos clearfix">
                        <span class="direct-chat-name @if($message->user_id === Auth::id()) float-right @else float-left @endif">{{ $message->user->name }}</span>
                        <span class="direct-chat-timestamp @if($message->user_id === Auth::id()) float-left @else float-right @endif">{{ $message->created_at->format('H:i:s') }}</span>
                    </div>
                    <img class="direct-chat-img" src="{{ $message->user->adminlte_image() }}" alt="User profile picture">
                    <div class="direct-chat-text">
                        {{ $message->message }}
                    </div>
                </div>
            @endforeach
        </div>
        <form id="chat-form" action="{{ route('chat.store') }}" method="POST">
            @csrf
            <div class="input-group">
                <input type="text" name="message" id="message" placeholder="Escribe el mensaje ..." class="form-control">
                <span class="input-group-append">
                    <button type="submit" class="btn btn-primary">Enviar</button>
                </span>
            </div>
        </form>
    </div>
</aside>
<script src="https://js.pusher.com/7.0/pusher.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicialización de Pusher
        var pusher = new Pusher('47bcab0f09430ca10552', {
            cluster: 'sa1',
            encrypted: true
        });

        var channel = pusher.subscribe('chat');

        channel.bind('App\\Events\\MessageSent', function(data) {
            let chatBox = document.getElementById('chat-box');
            let newMessage = `
                <div class="direct-chat-msg ${data.message.user_id === {{ auth()->id() }}? 'right' : ''}">
                    <div class="direct-chat-infos clearfix">
                        <span class="direct-chat-name ${data.message.user_id === {{ auth()->id() }}? 'float-right' : 'float-left'}">${data.message.user.name}</span>
                        <span class="direct-chat-timestamp ${data.message.user_id === {{ auth()->id() }}? 'float-left' : 'float-right'}">${new Date(data.message.created_at).toLocaleTimeString()}</span>
                    </div>
                    <img class="direct-chat-img" src="${data.message.user.adminlte_image}" alt="User profile picture">
                    <div class="direct-chat-text">
                        ${data.message.message}
                    </div>
                </div>
            `;
            chatBox.insertAdjacentHTML('beforeend', newMessage);
            chatBox.scrollTop = chatBox.scrollHeight;
        });

        // Envío del formulario
        document.getElementById('chat-form').addEventListener('submit', function(e) {
            e.preventDefault();

            let messageInput = document.getElementById('message');
            let message = messageInput.value.trim();

            if (message === '') return;

            let formData = new FormData(this);

            fetch(this.action, {
                method: this.method,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: formData
            })
           .then(response => response.json())
           .catch(error => console.error('Error:', error));

            messageInput.value = '';
        });
    });
</script>
