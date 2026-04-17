<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    public $pdfPath;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($invoice, $pdfPath)
    {
        $this->invoice = $invoice;
        $this->pdfPath = $pdfPath;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $fileName = 'Invoice-' . ($this->invoice->invoice_number ?? $this->invoice->id) . '.pdf';

        return $this->subject('Your Invoice from Marvel Batteries')
                    ->html('Dear ' . ($this->invoice->customer->name ?? 'Customer') . ',<br><br>Please find attached your invoice.<br><br>Thank you.')
                    ->attach($this->pdfPath, [
                        'as' => $fileName,
                        'mime' => 'application/pdf',
                    ]);
    }
}
