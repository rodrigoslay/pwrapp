<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkOrderStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $workOrder;

    public function __construct($workOrder)
    {
        $this->workOrder = $workOrder;
    }

    public function broadcastOn()
    {
        return new Channel('work-orders');
    }

    public function broadcastWith()
    {
        return [
            'license_plate' => $this->workOrder->vehicle->license_plate,
            'status' => $this->workOrder->status,
            'time_elapsed' => $this->workOrder->created_at->diffForHumans(),
            'message' => $this->getStatusMessage($this->workOrder->status)
        ];
    }

    private function getStatusMessage($status)
    {
        switch ($status) {
            case 'Iniciado':
                return 'Su vehículo está esperando su turno para entrar al taller.';
            case 'Incidencias':
                return 'Se encontraron fallos en su diagnóstico, diríjase donde el ejecutivo.';
            case 'En Proceso':
            case 'Aprobado':
            case 'Rechazado':
            case 'Parcial':
                return 'Nuestros técnicos están realizando los servicios solicitados.';
            case 'Completado':
                return 'Su vehículo está listo para ser retirado, diríjase donde su ejecutivo.';
            default:
                return '';
        }
    }
}
