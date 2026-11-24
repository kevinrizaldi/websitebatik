<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    /**
     * Build the mail representation of the notification.
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $expireMinutes = (int) config('auth.passwords.users.expire', 60);
        $token = $this->token;

        return (new MailMessage)
            ->subject('Permintaan Reset Kata Sandi – Hamzah Style Official')
            ->greeting('Halo, '.$notifiable->name.'! 👋')
            ->line('Kami menerima permintaan untuk mereset kata sandi akun Hamzah Style Official Anda.')
            ->line('**Jika menggunakan website**, klik tombol di bawah:')
            ->action('Reset Kata Sandi via Website', $resetUrl)
            ->line('---')
            ->line('**Jika menggunakan aplikasi mobile (Hamzah Style App)**, salin kode token berikut dan tempelkan ke kolom "Kode Token Reset" di aplikasi:')
            ->line('```')
            ->line($token)
            ->line('```')
            ->line('Kode ini berlaku selama **'.$expireMinutes.' menit** dan hanya dapat digunakan sekali.')
            ->line('Jika Anda tidak merasa meminta reset kata sandi, abaikan email ini. Akun Anda tetap aman.')
            ->salutation('Salam hangat, Tim Hamzah Style Official');
    }
}

