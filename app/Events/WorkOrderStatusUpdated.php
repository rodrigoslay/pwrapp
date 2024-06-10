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
                return 'Se ha informado a los mecánicos que su vehículo está listo para entrar al taller.';
            case 'En Proceso':
                return 'Nuestros mecánicos están trabajando en su vehículo.';
            case 'Incidencias':
                return 'Acérquese a su ejecutivo, su vehículo tiene incidencias.';
            case 'Completado':
                return 'Su vehículo está listo, acérquese a su ejecutivo.';
            case 'Aprobado':
                return 'Se indicó al mecánico que las incidencias están aprobadas.';
            case 'Parcial':
                return 'Se indicó al mecánico que algunas incidencias fueron aprobadas.';
            case 'Rechazado':
                return 'Se indicó al mecánico que las incidencias están rechazadas.';
            default:
                return '';
        }
    }
}
