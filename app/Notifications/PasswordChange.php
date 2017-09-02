<?php

namespace App\Notifications;

use App\Org;
use App\Person;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordChange extends Notification
{
    use Queueable;

    protected $person;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct (Person $person) {
        $this->person = $person;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via ($notifiable) {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail ($notifiable) {
        $o = Org::find($this->person->defaultOrgID);
        $name = $o->orgName;
        return (new MailMessage)
            ->subject('Your mCentric Password')
            ->line('Your mCentric password was recently changed.')
            ->line('If you initiated this change, you can delete this email.')
            ->line('If you did not, you should reset it now using the button below.')
            ->action('Password Reset', url('/password/reset?e='.$this->person->login))
            ->line("Thank you for using mCentric with $name");
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function toArray ($notifiable) {
        return [
            //
        ];
    }
}
