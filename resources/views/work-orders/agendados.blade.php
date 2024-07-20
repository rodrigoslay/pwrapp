@extends('adminlte::page')

@section('title', 'Agendadas')

@section('content_header')
    <h1>Agendadas</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div id="calendar"></div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/fullcalendar/main.min.css') }}">
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script src="{{ asset('vendor/fullcalendar/main.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: @json($events),
                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    if (info.event.url) {
                        window.open(info.event.url, "_blank");
                    }
                }
            });
            calendar.render();
        });
    </script>
@stop
