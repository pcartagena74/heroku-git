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
    protected $import_detail;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    // public function __construct(Person $person, $import_detail)
    public function __construct(Person $person, $import_detail)
    {
        $this->person        = $person;
        $this->import_detail = $import_detail->refresh();
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
        $i_d  = $this->import_detail;
        $name = trim($this->person->firstName . ' ' . $this->person->lastName);
        if (empty($name)) {
            $name = $this->person->login;
        }
        if ($i_d->total == 0) {
            return (new MailMessage)
                ->subject(trans('messages.notifications.member_import.subject_failed'))
                ->line(trans('messages.notifications.member_import.imp_failed',
                    ['user' => $name, 'file_name' => $i_d->file_name, 'completed_date' => $i_d->completed_at]));
        }
        $import_message = trans('messages.notifications.member_import.imp_success',
            ['user' => $name, 'file_name' => $i_d->file_name, 'completed_date' => $i_d->completed_at]);
        $subject = trans('messages.notifications.member_import.subject')
        if ($i_d->total > ($i_d->inserted + $i_d->updated)) {
            $import_message = trans('messages.notifications.member_import.imp_warning',
                ['user' => $name, 'file_name' => $i_d->file_name, 'completed_date' => $i_d->completed_at]);
            $subject = trans('messages.notifications.member_import.subject_warning')
        }
        return (new MailMessage)
            ->subject($subject)
            ->line($import_message)
            ->line(trans('messages.notifications.member_import.total',
                ['total' => $i_d->total]))
            ->line(trans('messages.notifications.member_import.inserted',
                ['inserted' => $i_d->inserted]))
            ->line(trans('messages.notifications.member_import.updated',
                ['updated' => $i_d->updated]))
            ->line(trans('messages.notifications.member_import.failed',
                ['failed' => $i_d->failed]))
            ->line(trans('messages.notifications.member_import.failed_record',
                ['total' => $i_d->failed_record]));
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
