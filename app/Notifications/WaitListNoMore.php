<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Registration;
use App\Event;
use App\Org;

class WaitListNoMore extends Notification
{
    use Queueable;

    protected $reg;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Registration $reg)
    {
        $this->reg = $reg;
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
        $event  = Event::find($this->reg->eventID);
        $org    = Org::find($event->orgID);

        return (new MailMessage)
            ->subject(trans('messages.notifications.WLNM.subject', ['org' => $org->orgName]))
            ->line(trans('messages.notifications.WLNM.line1', ['event' => $event->eventName]))
            ->line(trans('messages.notifications.WLNM.line2'))
            ->action(trans('messages.notifications.WLNM.action'), url(env('APP_URL').'/confirm_registration/'.$this->reg->rfID))
            ->line(trans('messages.notifications.thanks'));
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