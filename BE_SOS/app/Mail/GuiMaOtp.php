<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GuiMaOtp extends Mailable
{
    use Queueable, SerializesModels;

    public $maOtp;
    public $hoTen;

    public function __construct($maOtp, $hoTen = null)
    {
        $this->maOtp = $maOtp;
        $this->hoTen = $hoTen;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Mã xác nhận đặt lại mật khẩu - FE SOS',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ma-otp',
            with: [
                'maOtp' => $this->maOtp,
                'hoTen' => $this->hoTen,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}