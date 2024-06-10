<?php

namespace App\Listeners;

use App\Events\WorkOrderStatusUpdated;

class SendWorkOrderStatusNotification
{
    /**
     * Crear el escuchador de eventos.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Manejar el evento.
     *
     * @param  WorkOrderStatusUpdated  $event
     * @return void
     */
    public function handle(WorkOrderStatusUpdated $event)
    {
        // Puedes agregar aquí cualquier lógica adicional que quieras ejecutar cuando se dispare el evento.
        // Por ejemplo, podrías enviar una notificación por correo electrónico:
        //
        // \Mail::to($event->workOrder->client->email)->send(new \App\Mail\WorkOrderStatusMail($event->workOrder));
        //
        // O registrar una actividad en el sistema:
        //
        // \Log::info('Estado de la orden de trabajo actualizado: ' . $event->workOrder->id);
    }
}
