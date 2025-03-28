<?php

namespace App\Mail;

use App\Models\Seller;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailySalesReport extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The seller instance.
     *
     * @var \App\Models\Seller
     */
    public $seller;

    /**
     * The sales data.
     *
     * @var array
     */
    public $salesData;

    /**
     * The report date.
     *
     * @var string
     */
    public $reportDate;

    /**
     * Create a new message instance.
     *
     * @param \App\Models\Seller $seller
     * @param array $salesData
     * @param string $reportDate
     */
    public function __construct(Seller $seller, array $salesData, string $reportDate)
    {
        $this->seller = $seller;
        $this->salesData = $salesData;
        $this->reportDate = $reportDate;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Relatório Diário de Vendas - ' . $this->reportDate,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-sales-report',
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
