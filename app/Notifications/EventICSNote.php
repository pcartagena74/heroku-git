<?php

namespace App\Notifications;

use App\Models\Event;
use App\Models\Org;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventICSNote extends Notification
{
    use Queueable;

    protected $event;

    protected $org;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->org = Org::find($event->orgID);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)->markdown('notifications.ics_note', [
            'event' => $this->event,
            'logoPath' => $this->org->logo_path(),
            'orgURL' => $this->org->org_url(),
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
