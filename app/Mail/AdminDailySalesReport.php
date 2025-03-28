<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminDailySalesReport extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Os dados de vendas do dia.
     *
     * @var array
     */
    public $salesData;

    /**
     * A data do relatu00f3rio.
     *
     * @var string
     */
    public $reportDate;

    /**
     * Create a new message instance.
     *
     * @param array $salesData
     * @param string $reportDate
     */
    public function __construct(array $salesData, string $reportDate)
    {
        $this->salesData = $salesData;
        $this->reportDate = $reportDate;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Resumo de Vendas Diu00e1rias - {$this->reportDate}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-daily-sales-report',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
