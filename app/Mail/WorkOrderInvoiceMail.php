<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\WorkOrder;

class WorkOrderInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $workOrder;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(WorkOrder $workOrder)
    {
        $this->workOrder = $workOrder;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Gracias por su visita a Powercars')
                    ->view('emails.work_order_invoice');
    }
}
