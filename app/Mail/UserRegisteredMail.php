<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\URL;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;

class UserRegisteredMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp;

    public function __construct(public User $user, ?string $otp = null)
    {
        $this->otp = $otp ?? rand(100000, 999999);

        // Si l'OTP n'était pas fourni, on le sauvegarde
        if (is_null($otp)) {
            $this->user->email_otp = $this->otp;
            $this->user->email_otp_expires_at = now()->addMinutes(10);
            $this->user->save();
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 Vérification de votre e-mail'
        );
    }

    public function content(): Content
    {
        $url = $this->getUrlSigned();

        return new Content(
            markdown: 'mail.user-registered-mail',
            with: [
                'user' => $this->user,
                'url' => $url,
                'otp' => $this->otp,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }

    private function getUrlSigned(): string
    {
        $signedUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $this->user->id,
                'hash' => sha1($this->user->email),
            ]
        );

        // On construit l’URL vers le frontend
        $parsed = parse_url($signedUrl);
        parse_str($parsed['query'] ?? '', $queryParams);

        return config('app.frontend_url') . '/verify-email/' . $this->user->id . '/' . sha1($this->user->email) . '?' . http_build_query($queryParams);
    }
}


