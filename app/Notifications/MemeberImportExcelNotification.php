<?php

namespace App\Notifications;

use App\Person;
use App\User;
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
        $user = User::where('id', $this->person->personID)->get()->first();
        \App::setLocale($user->locale);
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
        } else if ($i_d->failed > 0) {
            $import_message = trans('messages.notifications.member_import.imp_warning',
                ['user' => $name, 'file_name' => $i_d->file_name, 'completed_date' => $i_d->completed_at]);
            $subject = trans('messages.notifications.member_import.subject_warning');
            $mail    = new MailMessage;
            $mail->subject($subject);
            $mail->line($import_message);
            $mail->line(trans('messages.notifications.member_import.total',
                ['total' => $i_d->total]));
            $mail->line(trans('messages.notifications.member_import.inserted',
                ['inserted' => $i_d->inserted]));
            $mail->line(trans('messages.notifications.member_import.updated',
                ['updated' => $i_d->updated]));
            $mail->line(trans('messages.notifications.member_import.failed',
                ['failed' => $i_d->failed]));

            if (!empty($i_d->failed_records)) {
                $records = json_decode($i_d->failed_records);
                // var_dump($records);
                foreach ($records as $key => $value) {
                    // var_dump($value);
                    $str = '';
                    if (!empty($value->reason)) {
                        $str .= 'Reason: ' . $value->reason;
                    }
                    if (!empty($value->pmi_id)) {
                        $str .= ' PMI ID: ' . $value->pmi_id;
                    } else {
                        if (!empty($value->first_name)) {
                            $str .= ' First Name: ' . $value->first_name;
                        }
                        if (!empty($value->last_name)) {
                            $str .= ' Last Name: ' . $value->last_name;
                        }
                        if (empty($value->first_name) && empty($value->last_name) && !empty($value->primary_email)) {
                            $str .= ' Primary Email: ' . $value->primary_email;
                        }
                        if (empty($value->first_name) && empty($value->last_name) && empty($value->primary_email) && !empty($value->alternate_email)) {
                            $str .= ' Alternate Email: ' . $value->alternate_email;
                        }
                        if (empty($value->first_name) && empty($value->last_name) && empty($value->primary_email) && empty($value->primary_email) && empty($value->alternate_email)) {
                            $str .= ' ' . trans('messages.notifications.member_import.no_identifier');
                        }
                    }
                    $mail->line($str);
                }

            }
            return $mail;
        } else {
            $import_message = trans('messages.notifications.member_import.imp_success',
                ['user' => $name, 'file_name' => $i_d->file_name, 'completed_date' => $i_d->completed_at]);
            $subject = trans('messages.notifications.member_import.subject');
            $mail    = new MailMessage;
            $mail->subject($subject);
            $mail->line($import_message);
            $mail->line(trans('messages.notifications.member_import.total',
                ['total' => $i_d->total]));
            $mail->line(trans('messages.notifications.member_import.inserted',
                ['inserted' => $i_d->inserted]));
            $mail->line(trans('messages.notifications.member_import.updated',
                ['updated' => $i_d->updated]));
            return $mail;
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
                ['records' => $i_d->failed_records]));
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
