<?php

namespace App\Notifications;

use App\Person;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemeberImportExcelNotification extends Notification
{
    use Queueable;

    protected $person;
    protected $records;
    public $name;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    // public function __construct(Person $person, $records)
    public function __construct(Person $person, $records)
    {
        $this->person  = $person;
        $this->records = $records;
        $this->name = $person->showDisplayName();
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
        /*
        $name = trim($this->person->firstName . ' ' . $this->person->lastName);
        if (empty($name)) {
            $name = $this->person->login;
        }
        */
        return (new MailMessage)
            ->subject(trans('messages.notifications.member_import.subject'))
            ->line(trans('messages.notifications.member_import.line1', ['user' => $this->name, 'count' => $this->records]));
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
