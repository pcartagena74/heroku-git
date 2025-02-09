<?php

namespace App\Notifications;

use App\Models\Person;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemeberImportExcelNotification extends Notification
{
    use Queueable;

    protected $person;

    protected $import_detail;

    protected $records;

    public $name;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Person $person, $import_detail)
    {
        $this->person = $person;
        $this->name = $person->showDisplayName();
        $this->import_detail = $import_detail->refresh();
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
        $user = User::find($this->person->personID);
        \App::setLocale($user->locale);
        // $o     = Org::find($this->person->defaultOrgID);
        // $name  = $o->orgName;
        // $ename = $this->event->eventName;
        $i_d = $this->import_detail;
        $name = trim($this->name);
        if (empty($name)) {
            $name = $this->person->login;
        }
        if ($i_d->total == 0) {
            return (new MailMessage)
                ->subject(trans('messages.notifications.member_import.subject_failed'))
                ->line(trans('messages.notifications.member_import.imp_failed',
                    ['user' => $this->name, 'file_name' => $i_d->file_name, 'completed_date' => $i_d->completed_at]));
        } elseif ($i_d->failed > 0) {
            $import_message = trans('messages.notifications.member_import.imp_warning',
                ['user' => $this->name, 'file_name' => $i_d->file_name, 'completed_date' => $i_d->completed_at]);
            $subject = trans('messages.notifications.member_import.subject_warning');
            $mail = new MailMessage;
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

            if (! empty($i_d->failed_records)) {
                $records = json_decode($i_d->failed_records);
                // var_dump($records);
                foreach ($records as $key => $value) {
                    // var_dump($value);
                    $str = '';
                    if (! empty($value->reason)) {
                        $str .= 'Reason: '.$value->reason;
                    }
                    if (! empty($value->pmi_id)) {
                        $str .= ' PMI ID: '.$value->pmi_id;
                    } else {
                        if (! empty($value->first_name)) {
                            $str .= ' First Name: '.$value->first_name;
                        }
                        if (! empty($value->last_name)) {
                            $str .= ' Last Name: '.$value->last_name;
                        }
                        if (empty($value->first_name) && empty($value->last_name) && ! empty($value->primary_email)) {
                            $str .= ' Primary Email: '.$value->primary_email;
                        }
                        if (empty($value->first_name) && empty($value->last_name)
                            && empty($value->primary_email) && ! empty($value->alternate_email)) {
                            $str .= ' Alternate Email: '.$value->alternate_email;
                        }
                        if (empty($value->first_name) && empty($value->last_name) && empty($value->primary_email) && empty($value->primary_email) && empty($value->alternate_email)) {
                            $str .= ' '.trans('messages.notifications.member_import.no_identifier');
                        }
                    }
                    $mail->line($str);
                }
                if (! empty($i_d->other)) {
                    $mail->line(trans('messages.notifications.member_import.rel_update'));
                    $others = json_decode($i_d->other);
                    foreach ($others as $key => $other) {
                        $str = '';
                        if (! empty($other->reldate1_new)) {
                            $str .= 'PMI-JoinDate Old : '.$other->reldate1_old.' New : '.$other->reldate1_new.' ';
                        }
                        if (! empty($other->reldate2_new)) {
                            $str .= 'Chap-JoinDate Old: '.$other->reldate2_old.' New : '.$other->reldate2_new;
                        }
                        $mail->line('PMI ID : '.$other->pmi_id.' '.$str);
                    }
                }
            }

            return $mail;
        } else {
            $import_message = trans('messages.notifications.member_import.imp_success',
                ['user' => $this->name, 'file_name' => $i_d->file_name, 'completed_date' => $i_d->completed_at]);
            $subject = trans('messages.notifications.member_import.subject');
            $mail = new MailMessage;
            $mail->subject($subject);
            $mail->line($import_message);
            $mail->line(trans('messages.notifications.member_import.total',
                ['total' => $i_d->total]));
            $mail->line(trans('messages.notifications.member_import.inserted',
                ['inserted' => $i_d->inserted]));
            $mail->line(trans('messages.notifications.member_import.updated',
                ['updated' => $i_d->updated]));
            if (! empty($i_d->other)) {
                $mail->line(trans('messages.notifications.member_import.rel_update'));
                $others = json_decode($i_d->other);
                foreach ($others as $key => $other) {
                    $str = '';
                    if (! empty($other->reldate1_new)) {
                        $str .= 'PMIJD Old : '.$other->reldate1_old.' New : '.$other->reldate1_new.' ';
                    }
                    if (! empty($other->reldate2_new)) {
                        $str .= 'ChapJD Old: '.$other->reldate2_old.' New : '.$other->reldate2_new;
                    }
                    $mail->line('PMI ID : '.$other->pmi_id.' '.$str);
                }
            }

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
     * @param  mixed  $notifiable
     */
    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
