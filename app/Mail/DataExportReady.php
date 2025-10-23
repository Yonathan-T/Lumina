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
    public $totalItems; // Renamed from totalEntries to be generic
    public $exportedAt;
    public $downloadUrl;
    public $expiresAt;
    public $exportType; // New property for conditional rendering

    public function __construct($userName, $totalItems, $downloadUrl, $expiresAt, $exportType = 'entries')
    {
        $this->userName = $userName;
        $this->totalItems = $totalItems;
        $this->downloadUrl = $downloadUrl;
        $this->expiresAt = $expiresAt;
        $this->exportedAt = now()->format('F j, Y \a\t g:i A');
        $this->exportType = $exportType; // Default to 'entries' if not specified
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->exportType === 'conversations' 
                ? 'Your Lumina Chat Export is Ready! 📦'
                : 'Your Lumina Data Export is Ready! 📦',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.data-export-ready',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}