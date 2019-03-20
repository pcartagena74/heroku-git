<?php

namespace App\Http\Controllers;

use App\RegSession;
use Illuminate\Http\Request;
use Excel;
use App\Event;
use App\Registration;

class DownloadController extends Controller
{
    public function nametags(Event $event)
    {
        $nametags = [];
        $regs = Registration::where('eventID', '=', $event->eventID)
            //->whereHas('regfinance', function($q){ $q->where('pmtRecd', '=', 1); })
            ->with('ticket', 'person', 'person.orgperson')->get();

        $tag_headers = ['RegID', trans('messages.fields.prefName'), trans('messages.fields.lastName'), trans('messages.headers.isFirst'),
            trans('messages.headers.email'), trans('messages.fields.ticket'), // trans('messages.headers.disc_code'),
            trans('messages.headers.comp'), trans('messages.fields.title'), ucwords(trans('messages.headers.ind')),
            trans('messages.headers.allergens'), trans('messages.fields.pmi_id'), trans('messages.headers.isAuthPDU'),
            trans('messages.headers.canNetwork'), trans('messages.headers.bal_due2')];

        $nametags[] = $tag_headers;

        foreach ($regs as $r) {
            $r->person->load('orgperson');
            $nametags[] = array($r->regID, $r->person->prefName, $r->person->lastName, $r->isFirstEvent, $r->person->login,
                $r->ticket->ticketLabel, $r->person->compName, $r->person->title, $r->person->indName,
                $r->person->allergenInfo . "; " . $r->person->allergenNote, $r->person->orgperson->OrgStat1, $r->isAuthPDU,
                $r->canNetwork, $r->regStatus == trans('messages.reg_status.pending') ? trans('messages.yesno_check.yes') : '');
        }

        Excel::create('nametag_data', function ($excel) use ($nametags) {
            $excel->setTitle('Name Tag Data');
            $excel->setCreator('mCentric')->setCompany('Efcico Corporation dba mCentric');
            $excel->setDescription('Name Tag Data');
            $excel->sheet('Name Tag Data', function ($sheet) use ($nametags) {
                $sheet->fromArray($nametags, null, 'A1', false, false);
            });
        })->download('csv');
    }

    public function pdu_list(Event $event)
    {
        $nametags = [];
        $regs = RegSession::where('eventID', '=', $event->eventID)
            //->whereHas('regfinance', function($q){ $q->where('pmtRecd', '=', 1); })
            ->with('person', 'person.orgperson', 'registration')->get();

        $tag_headers = ['RegSessID', trans('messages.fields.firstName'), trans('messages.fields.lastName'), // trans('messages.headers.isFirst'),
            //trans('messages.headers.email'), trans('messages.fields.ticket'), // trans('messages.headers.disc_code'),
            //trans('messages.headers.comp'), trans('messages.fields.title'), ucwords(trans('messages.headers.ind')),
            //trans('messages.headers.allergens'),
            trans('messages.fields.pmi_id'), trans('messages.headers.isAuthPDU'),
            //trans('messages.headers.canNetwork'), trans('messages.headers.bal_due2')
        ];

        $nametags[] = $tag_headers;

        foreach ($regs as $r) {
            $r->person->load('orgperson');
            $nametags[] = array($r->regID, $r->person->firstName, $r->person->lastName, // $r->isFirstEvent, $r->person->login,
                // $r->ticket->ticketLabel, $r->person->compName, $r->person->title, $r->person->indName,
                // $r->person->allergenInfo . "; " . $r->person->allergenNote,
                $r->person->orgperson->OrgStat1, $r->registration->isAuthPDU,
                // $r->canNetwork, $r->regStatus == trans('messages.reg_status.pending') ? trans('messages.yesno_check.yes') : ''
            );
        }

        Excel::create('pdu_data', function ($excel) use ($nametags) {
            $excel->setTitle('PDU Data');
            $excel->setCreator('mCentric')->setCompany('Efcico Corporation dba mCentric');
            $excel->setDescription('Name Tag Data');
            $excel->sheet('Name Tag Data', function ($sheet) use ($nametags) {
                $sheet->fromArray($nametags, null, 'A1', false, false);
            });
        })->download('csv');
    }
}
