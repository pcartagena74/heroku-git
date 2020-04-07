<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemeberImportExcelNotification extends Notification
{
    use Queueable;

    protected $person;
    protected $records;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    // public function __construct(Person $person, $records)
    public function __construct()
    {
        // $this->person  = $person;
        // $this->records = $records;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // $o     = Org::find($this->person->defaultOrgID);
        // $name  = $o->orgName;
        // $ename = $this->event->eventName;
        // $name = $this->person->firstName . ' ' . $this->person->lastName;
        return (new MailMessage)
            ->subject(trans('messages.notifications.member_import.subject'))
            ->line(trans('messages.notifications.member_import.line1'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
