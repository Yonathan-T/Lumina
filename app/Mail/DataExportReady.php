<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class DataExportReady extends Mailable
{
    use Queueable, SerializesModels;

    public $userName;
    public $totalEntries;
    public $exportedAt;
    public $downloadUrl;
    public $expiresAt;

    
    public function __construct($userName, $totalEntries, $downloadUrl, $expiresAt)
    {
        $this->userName = $userName;
        $this->totalEntries = $totalEntries;
        $this->downloadUrl = $downloadUrl;
        $this->expiresAt = $expiresAt;
        $this->exportedAt = now()->format('F j, Y \a\t g:i A');
    }

    
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Lumina Data Export is Ready! 📦',
        );
    }

  
    public function content(): Content
    {
        return new Content(
            view: 'emails.data-export-ready',
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
