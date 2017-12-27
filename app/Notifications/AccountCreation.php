<?php

namespace App\Notifications;

use App\Event;
use App\Org;
use App\Person;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AccountCreation extends Notification
{
    use Queueable;

    protected $person;
    protected $event;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct (Person $person, Event $event) {
        $this->person = $person;
        $this->event = $event;
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
        $ename = $this->event->eventName;
        return (new MailMessage)
            ->subject("Your mCentric Account ($name)")
            ->line("An mCentric account was recently created for you during registration for $ename.")
            ->line('If you initiated this change, you can delete this email.')
            ->action('Visit mCentric', env('APP_URL'))
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
