@extends('adminlte::page')

@section('title', 'Perfil de Usuario')

@section('content_header')
    <h1>Perfil de Usuario</h1>
@stop

@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

    <div class="container">
        <div class="row">
            <!-- Profile Image -->
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <img class="profile-user-img img-fluid img-circle"
                                 src="{{ auth()->user()->adminlte_image() }}"
                                 alt="User profile picture">
                        </div>

                        <h3 class="profile-username text-center">{{ $user->name }}</h3>

                        <p class="text-muted text-center">{{collect($user->getRoleNames())->implode(', ') }}</p>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>Correo Electrónico</b> <a class="float-right">{{ $user->email }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Miembro desde</b> <a class="float-right">{{ $user->created_at->format('d/m/Y') }}</a>
                            </li>
                        </ul>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>

            <!-- Edit Profile Form -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header p-2">
                        <ul class="nav nav-pills">
                            <!-- /.li class="nav-item"><a class="nav-link active" href="#settings" data-toggle="tab">Configuración</a></ -->
                            Modifica aqui tu Nombre, Contraseña y Foto de Perfil.
                        </ul>
                    </div><!-- /.card-header -->
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif
                        <div class="tab-content">
                            <div class="active tab-pane" id="settings">
                                <form class="form-horizontal" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="form-group row">
                                        <label for="name" class="col-sm-2 col-form-label">Nombre</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}" required>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="email" class="col-sm-2 col-form-label">Correo Electrónico</label>
                                        <div class="col-sm-10">
                                            <input type="id_email" class="form-control" id="id_email" name="id_email" value="{{ $user->email }}" disabled>
                                            <input type="hidden" name="email" value="{{ $user->email }}">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="password" class="col-sm-2 col-form-label">Contraseña</label>
                                        <div class="col-sm-10">
                                            <input type="password" class="form-control" id="password" name="password">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="password_confirmation" class="col-sm-2 col-form-label">Confirmar Contraseña</label>
                                        <div class="col-sm-10">
                                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="avatar" class="col-sm-2 col-form-label">Avatar</label>
                                        <div class="col-sm-10">
                                            <input type="file" class="form-control-file" id="avatar" name="avatar">
                                        </div>
                                    </div>
                                    <div class="form-group row d-none">
                                        <div class="offset-sm-2 col-sm-10">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="dark_mode" name="dark_mode" {{ $user->dark_mode ? 'checked' : '' }}>
                                                <label class="form-check-label" for="dark_mode">Modo Oscuro</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="offset-sm-2 col-sm-10">
                                            <button type="submit" class="btn btn-primary">Actualizar Perfil</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <!-- /.tab-pane -->
                        </div>
                        <!-- /.tab-content -->
                    </div><!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
        </div>
    </div>
@stop

@section('footer')
    Realizado por <a href="https://www.slaymultimedios.com/"><strong>Slay Multimedios</strong></a> - Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})<br>
    &copy; 2024 PWRTALLER Versión 1.0. Todos los derechos reservados.
@stop
@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop
@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    $(document).ready(function() {
        $('#dark_mode').change(function() {
            $(this).closest('form').submit();
        });
    });
</script>
@stop
