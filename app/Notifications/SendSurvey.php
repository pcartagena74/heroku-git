<?php

namespace App\Notifications;

use App\Event;
use App\Org;
use App\Person;
use App\RegSession;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SendSurvey extends Notification
{
    use Queueable;

    protected $person;
    protected $event;
    protected $rs;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Person $person, Event $event, RegSession $rs)
    {
        $this->person = $person;
        $this->event = $event;
        $this->rs = $rs;
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
        $o = Org::find($this->person->defaultOrgID);
        $name = $o->orgName;
        $ename = $this->event->event_type->etName;
        return (new MailMessage)
            ->subject(trans('messages.notifications.SS.subject', ['org' => $name, 'event_type' => $ename]))
            ->line(trans('messages.notifications.SS.line1'))
            ->line(trans('messages.notifications.SS.line2'))
            ->action(trans('messages.notifications.SS.action'), env('APP_URL')."/rs_survey/" . $this->rs->id)
            ->line(trans('messages.notifications.thanks', ['org' => $name]));
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
