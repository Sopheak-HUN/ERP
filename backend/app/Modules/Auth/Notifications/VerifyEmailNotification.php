<?php

declare(strict_types=1);

namespace App\Modules\Auth\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

final class VerifyEmailNotification extends BaseVerifyEmail
{
    /**
     * @param  MustVerifyEmail  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        $url = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify your email address')
            ->line('Please confirm your email address by clicking the button below.')
            ->action('Verify email', $url)
            ->line('If you did not create an account, no further action is required.');
    }

    /**
     * Builds a signed backend URL the user clicks from email. On success the
     * controller redirects to the frontend.
     *
     * @param  MustVerifyEmail  $notifiable
     */
    protected function verificationUrl($notifiable): string
    {
        $id = $notifiable instanceof Authenticatable ? $notifiable->getAuthIdentifier() : null;

        return URL::temporarySignedRoute(
            'api.v1.auth.email.verify',
            Carbon::now()->addMinutes((int) Config::get('auth.verification.expire', 60)),
            [
                'id' => $id,
                'hash' => sha1((string) $notifiable->getEmailForVerification()),
            ],
        );
    }
}
