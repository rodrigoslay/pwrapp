@extends('adminlte::master')

@section('adminlte_css')
    @stack('css')
    @yield('css')
@stop

@section('body_class', 'hold-transition sidebar-mini layout-fixed')

@section('body')
    @if(config('adminlte.preloader.enabled'))
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="{{ asset(config('adminlte.preloader.img.path')) }}" alt="{{ config('adminlte.preloader.img.alt') }}" height="{{ config('adminlte.preloader.img.height') }}" width="{{ config('adminlte.preloader.img.width') }}">
        </div>
    @endif

    <div class="wrapper">

        {{-- Main Header --}}
        @include('adminlte::partials.navbar.navbar')

        {{-- Left side column. contains the logo and sidebar --}}
        @include('adminlte::partials.sidebar.left-sidebar')

        {{-- Content Wrapper. Contains page content --}}
        <div class="content-wrapper">

            {{-- Content Header (Page header) --}}
            <section class="content-header">
                <div class="{{ config('adminlte.classes_content_header', 'container-fluid') }}">
                    @yield('content_header')
                </div><!-- /.container-fluid -->
            </section>

            {{-- Main content --}}
            <section class="content">
                <div class="{{ config('adminlte.classes_content', 'container-fluid') }}">
                    @yield('content')
                </div><!-- /.container-fluid -->
            </section><!-- /.content -->

        </div><!-- /.content-wrapper -->

        {{-- Control Sidebar --}}
       @include('adminlte::partials.sidebar.right-sidebar')
       @include('chat.right-sidebar')

        {{-- Main Footer --}}
        @include('adminlte::partials.footer.footer')

    </div><!-- ./wrapper -->
@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')
@stop
