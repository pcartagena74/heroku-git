<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class RegFinance extends Model
{
    use SoftDeletes;

    //use LogsActivity;
    // The table
    protected $table = 'reg-finance';

    protected $primaryKey = 'regID';

    const CREATED_AT = 'createDate';

    const UPDATED_AT = 'cancelDate';

    protected $casts = [
        'createDate' => 'datetime',
        'cancelDate' => 'datetime',
    ];

    //protected static $logAttributes = ['confirmation', 'pmtRecd', 'status', 'cost'];
    //protected static $ignoreChangedAttributes = ['createDate', 'cancelDate'];

    public function event()
    {
        return $this->belongsTo(Event::class, 'eventID', 'eventID');
    }

    public function person()
    {
        return $this->belongsTo(Person::class, 'personID', 'personID');
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'rfID', 'regID');
    }

    /**
     * @return mixed
     */
    public function receipt_url()
    {
        $receipt_filename = "$this->eventID/$this->confirmation.pdf";

        try {
            if (Storage::disk('s3_receipts')->exists($receipt_filename)) {
                $receipt_url = Storage::disk('s3_receipts')->url($receipt_filename);
            }
        } catch (Exception $e) {
            $receipt_url = '#';
        }

        return $receipt_url;
    }
}
