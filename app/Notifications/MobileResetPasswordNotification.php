<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class MobileResetPasswordNotification extends Notification
{
    public function __construct(
        private readonly string $otp,
        private readonly int $expireMinutes = 10,
    ) {}

    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kode OTP Reset Kata Sandi – Hamzah Style App')
            ->greeting('Halo, ' . $notifiable->name . '! 👋')
            ->line('Kami menerima permintaan reset kata sandi dari **aplikasi Hamzah Style**.')
            ->line('Gunakan kode OTP berikut untuk melanjutkan:')
            ->line('# ' . $this->otp)
            ->line('Kode ini berlaku selama **' . $this->expireMinutes . ' menit** dan hanya dapat digunakan sekali.')
            ->line('Jika Anda tidak meminta reset kata sandi, abaikan email ini. Akun Anda tetap aman.')
            ->salutation('Salam hangat, Tim Hamzah Style');
    }
}
