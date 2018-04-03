<?php

namespace App\Notifications;

use App\Org;
use App\Person;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SetYourPassword extends Notification
{
    use Queueable;

    protected $person;
    protected $o;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Person $person)
    {
        $this->person = $person;
        $this->o = Org::find($this->person->defaultOrgID);
    }


    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $name = $this->o->orgName;
        return (new MailMessage)
            ->subject('Your mCentric Account: How to reset your password')
            ->line('An mCentric account was setup for you by ' . $name . '.')
            ->line('If you have not yet set its password or do not remember it, you reset it now using the button below.')
            ->line('If you do not need to reset your password, delete this email.')
            ->action('Password Reset', url('/password/reset?e='.$this->person->login))
            ->line("Thank you for using mCentric with $name");
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
