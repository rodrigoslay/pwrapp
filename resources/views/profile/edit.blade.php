@extends('adminlte::page')

@section('title', 'Editar Perfil | Dashboard')

@section('content_header')
    <h1>Editar Perfil</h1>
@stop

@section('content')
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        <div class="row">
            <div class="col-12 col-md-6">
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card">
                        <div class="card-header">
                            <h5>Información del Perfil</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" value="{{ old('name', $user->name) }}" required>
                                @if($errors->has('name'))
                                    <span class="text-danger">{{ $errors->first('name') }}</span>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" required>
                                @if($errors->has('email'))
                                    <span class="text-danger">{{ $errors->first('email') }}</span>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" name="password" placeholder="Dejar en blanco para mantener la actual">
                                @if($errors->has('password'))
                                    <span class="text-danger">{{ $errors->first('password') }}</span>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                                <input type="password" class="form-control" name="password_confirmation" placeholder="Dejar en blanco para mantener la actual">
                            </div>
                            <div class="form-group">
                                <label for="avatar" class="form-label">Avatar</label>
                                <input type="file" class="form-control" name="avatar">
                                @if($errors->has('avatar'))
                                    <span class="text-danger">{{ $errors->first('avatar') }}</span>
                                @endif
                                @if($user->avatar)
                                    <div class="mt-2">
                                        <img src="{{ Storage::url($user->avatar) }}" alt="Avatar" class="img-thumbnail" width="100">
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop
