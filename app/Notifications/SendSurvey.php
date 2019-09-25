<?php

namespace App\Notifications;

use App\Event;
use App\EventSession;
use App\Org;
use App\Person;
use App\RegSession;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class SendSurvey extends Notification
{
    use Queueable;

    protected $person;
    protected $event;
    protected $rs;
    protected $es;

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
        $this->es = EventSession::find($rs->sessionID);
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
        $etype = $this->event->event_type->etName;
        $ename = $this->event->eventName;
        if(Lang::has('messages.event_types.'.$etype)){
            $etype = trans_choice('messages.event_types.'.$etype, 1);
        }
        $date = $this->event->eventStartDate->format('F jS');

        if($this->event->hasTracks > 0){
            return (new MailMessage)
                ->greeting(trans('messages.notifications.hello', ['firstName' => $this->person->showDisplayName()]))
                ->subject(trans('messages.notifications.SS.subject', ['org' => $name, 'event_type' => $etype]))
                ->line(trans('messages.notifications.SS.line1', ['etype' => $etype, 'ename' => $ename, 'date' => $date]))
                ->line(trans('messages.notifications.SS.line2'))
                ->line(trans('messages.notifications.SS.line3', ['name' => $this->es->sessionName]))
                ->action(trans('messages.notifications.SS.action'), env('APP_URL')."/rs_survey/" . $this->rs->id)
                ->line(trans('messages.notifications.thanks', ['org' => $name]));
        } else {
            return (new MailMessage)
                ->greeting(trans('messages.notifications.hello', ['firstName' => $this->person->showDisplayName()]))
                ->subject(trans('messages.notifications.SS.subject', ['org' => $name, 'event_type' => $etype]))
                ->line(trans('messages.notifications.SS.line1', ['etype' => $etype, 'ename' => $ename, 'date' => $date]))
                ->line(trans('messages.notifications.SS.line2'))
                ->action(trans('messages.notifications.SS.action'), env('APP_URL')."/rs_survey/" . $this->rs->id)
                ->line(trans('messages.notifications.thanks', ['org' => $name]));
        }
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
