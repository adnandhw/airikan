<?php

namespace App\Mail;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $transaction;
    public $isAdmin;

    /**
     * Create a new message instance.
     *
     * @param Transaction $transaction
     * @param bool $isAdmin
     */
    public function __construct(Transaction $transaction, $isAdmin = false)
    {
        $this->transaction = $transaction;
        $this->isAdmin = $isAdmin;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->isAdmin 
            ? 'Notifikasi Pesanan Baru #' . strtoupper(substr($this->transaction->id, 0, 8))
            : 'Konfirmasi Pesanan Anda #' . strtoupper(substr($this->transaction->id, 0, 8));

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order_notification',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
