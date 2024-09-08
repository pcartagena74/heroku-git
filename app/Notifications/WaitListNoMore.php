<?php

namespace App\Notifications;

use App\Models\Event;
use App\Models\Org;
use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WaitListNoMore extends Notification implements ShouldQueue
{
    use Queueable;

    protected $reg;

    public $name;

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
        $event = Event::find($this->reg->eventID);
        $org = Org::find($event->orgID);

        return (new MailMessage)
            ->greeting(trans('messages.notifications.hello', ['firstName' => $this->name]))
            ->subject(trans('messages.notifications.WLNM.subject', ['org' => $org->orgName]))
            ->line(trans('messages.notifications.WLNM.line1', ['event' => $event->eventName]))
            ->line(trans('messages.notifications.WLNM.line2'))
            ->action(trans('messages.notifications.WLNM.action'), url(env('APP_URL').'/confirm_registration/'.$this->reg->rfID))
            ->line(trans('messages.notifications.thanks', ['org' => $org->orgName]));
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
