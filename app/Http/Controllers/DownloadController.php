<?php

namespace App\Http\Controllers;

use App\DataExport;
use App\Event;
use App\EventSession;
use App\Org;
use App\Person;
use App\Registration;
use App\RegSession;
use Excel;
use Illuminate\Http\Request;

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
            trans('messages.headers.canNetwork'), trans('messages.headers.bal_due2'), trans('messages.headers.affiliation'),
            trans('messages.headers.membership'), ];

        // $nametags[] = $tag_headers;

        foreach ($regs as $r) {
            $r->person->load('orgperson');
            $nametags[] = [$r->regID, $r->person->prefName, $r->person->lastName, $r->isFirstEvent, $r->person->login,
                $r->ticket->ticketLabel, $r->person->compName, $r->person->title, $r->person->indName,
                $r->person->allergenInfo.'; '.$r->person->allergenNote, $r->person->orgperson->OrgStat1, $r->isAuthPDU,
                $r->canNetwork, $r->regStatus == 'pending' ? 'yes' : '', $r->affiliation, $r->membership, ];
        }

        return Excel::download(new DataExport($tag_headers, $nametags), 'nametag_data.csv');
    }

    public function email_list(Event $event)
    {
        $nametags = [];
        $org = Org::find($event->orgID);

        $regs = Registration::where('eventID', '=', $event->eventID)
            ->whereHas('regfinance', function ($q) {
                $q->where('pmtRecd', '=', 1);
            })
            ->with('ticket', 'person', 'person.orgperson')->get();

        $tag_headers = [trans('messages.fields.login')];

        // $nametags[] = $tag_headers;

        foreach ($regs as $r) {
            $p = Person::find($r->person->personID);
            $p->load('orgperson');

            $nametags[] = [$r->person->login];
        }

        return Excel::download(new DataExport($tag_headers, $nametags), 'email_data.csv');
    }

    public function pdu_list(Event $event, EventSession $es = null)
    {
        $nametags = [];
        $org = Org::find($event->orgID);

        if (null === $es) {
            $regs = RegSession::where('eventID', '=', $event->eventID)
                ->whereHas('registration', function ($q) {
                    $q->whereNull('deleted_at');
                })
                ->with('person', 'person.orgperson', 'registration', 'person.orgs')->get();
            $filename = 'pdu_data.csv';
        } else {
            $regs = RegSession::where([
                ['eventID', '=', $event->eventID],
                ['sessionID', '=', $es->sessionID],
            ])
                ->whereHas('registration', function ($q) {
                    $q->whereNull('deleted_at');
                })
                ->with('person', 'person.orgperson', 'registration', 'person.orgs')->get();
            $filename = "pdu_data_$es->sessionID.csv";
        }

        $tag_headers = ['RegSessID', trans('messages.fields.firstName'), trans('messages.fields.lastName'),
            trans('messages.fields.pmi_id'), trans('messages.headers.isAuthPDU'), ];

        // $nametags[] = $tag_headers;
        //dd($regs);

        foreach ($regs as $r) {
            $p = Person::find($r->person->personID);
            $p->load('orgperson');
            $r->load('registration');
            $x = $r->registration;

            $nametags[] = [$r->regID, $r->person->firstName, $r->person->lastName, $p->orgperson->OrgStat1, $x->isAuthPDU];
        }

        return Excel::download(new DataExport($tag_headers, $nametags), $filename);
    }
}
