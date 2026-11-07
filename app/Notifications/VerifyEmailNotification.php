<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verifikasi Email Akun Anda – Hamzah Style Official')
            ->greeting('Halo, ' . $notifiable->name . '! 👋')
            ->line('Terima kasih telah mendaftar di **Hamzah Style Official**.')
            ->line('Klik tombol di bawah untuk memverifikasi alamat email Anda.')
            ->action('Verifikasi Email Sekarang', $verificationUrl)
            ->line('Tautan ini akan kedaluwarsa dalam 60 menit.')
            ->line('Jika Anda tidak mendaftar, abaikan email ini.')
            ->salutation('Salam, Tim Hamzah Style Official');
    }
}

