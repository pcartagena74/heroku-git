<?php

namespace App\Notifications;

use App\RegFinance;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Event;
use App\Org;
use App\Person;

class EventReceipt extends Notification
{
    use Queueable;

    protected $rf;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct (RegFinance $rf) {
        $this->rf = $rf;
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
        $event  = Event::find($this->rf->eventID);
        $org    = Org::find($event->orgID);
        $person = Person::find($this->rf->personID);

        return (new MailMessage)
            ->subject('Event Registration')
            ->line("Thank you for registering for $event->eventName.")
            //  ->action('Notification Action', url('/'))
            ->line("Event Host: $event->contactOrg")
            ->line("Date/Time: $event->eventStartDate->format('n/j/Y g:i A')")
            ->line("Location: $event->contactOrg")
            ->line("For more information about this event, visit <a href='#'>the event page</a> or contact your <a href='mailto:$event->contactEmail'>event organizer</a>.");
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
