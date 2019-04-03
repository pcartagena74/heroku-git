<?php
/**
 * Comment: Added to have some global functions
 * Created: 8/25/2017
 */

use App\Email;
use App\Org;
use App\OrgPerson;
use App\Person;
use Intervention\Image\ImageManagerStatic as Image;
use GrahamCampbell\Flysystem\Facades\Flysystem;
use League\Flysystem\AdapterInterface;


/**
 * Takes the html contents from the summernote input field and parses out uploaded images for
 * storage in AWS media area associated with Org and updates the html to reference image URLs
 *
 * @param $html
 * @param Org $org
 * @return string
 */
function extract_images($html, $orgID){
    $dom = new \DOMDocument();
    $org = Org::find($orgID);
    $updated = 0;

    try {
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_PARSEHUGE);

        $images = $dom->getElementsByTagName('img');

        foreach($images as $img){
            $src = $img->getAttribute('src');

            if(preg_match('/data:image/', $src)){
                $updated = 1;
                // get the mimetype
                preg_match('/data:image\/(?<mime>.*?)\;/', $src, $groups);
                $mimetype = $groups['mime'];

                // Generating a random filename
                $filename = $img->getAttribute('data-filename');
                $filepath = "$org->orgPath/uploads/$filename";

                // @see http://image.intervention.io/api/
                $image = Image::make($src)
                    // resize if required
                    /* ->resize(300, 200) */
                    ->encode($mimetype, 100); 	// encode file to the specified mimetype

                //Flysystem::connection('s3_media')->put($event_filename, $contents);
                $s3m = Flysystem::connection('s3_media');
                //$s3m->put($filename, $image, ['visibility' => AdapterInterface::VISIBILITY_PUBLIC]);
                $s3m->put($filepath, $image->__toString());
                $new_src = $s3m->getAdapter()->getClient()->getObjectURL(env('AWS_BUCKET3'), $filepath);

                $img->removeAttribute('src');
                $img->removeAttribute('data-filename');
                $img->setAttribute('src', $new_src);
            }
        }
        if($updated){
            return $dom->saveHTML();
        } else {
            return $html;
        }
    } catch (Exception $exception) {
        request()->session()->flash('alert-danger', trans('messages.errors.html_error') . "<br /><pre>$exception</pre>");
        return $html;
    }
}

/**
 * Takes a model indicator and an array of variables, usually just 1, and performs a rudimentary existence check
 *
 * @param $model        Values: p for Person, e for Email, op for OrgPerson
 * @param $var_array    Contents:
 *                      + p:  firstName, lastName, login
 *                      + e:  login
 *                      + op: PMI ID
 */
function check_exists($model, $var_array){
    $details = null;
    switch ($model){
        case 'p':
            list($first, $last, $login) = $var_array;
            $p = Person::where([
                ['firstName', '=', $first],
                ['lastName', '=', $last]
            ])
                ->orWhere('login', '=', $login)
                ->orWhereHas('emails', function($q) use($login){
                    $q->where('emailADDR', '=', $login);
                })->get();
            if(count($p) > 0){
                $details = "<ul>";
                foreach($p as $x){
                    $details .= "<li>$x->firstName, $x->lastName, $x->login</li>";
                }
                $details .= "</ul>";
                dd($details);
                request()->session()->flash('alert-warning', trans_choice('messages.errors.exists', $model), ['details' => $details]);
                return 1;
            }
            break;
        case 'e':
            list($email) = $var_array;
            $e = Email::where('emailADDR', '=', $email)->first();
            if(null !== $e){
                request()->session()->flash('alert-warning', trans_choice('messages.errors.exists', $model), ['details' => $details]);
                return 1;
            }
            break;
        case 'op':
            list($pmiID) = $var_array;
            $op = OrgPerson::where('OrgStat1', '=', $pmiID)->first();
            if(null !== $op){
                request()->session()->flash('alert-warning', trans_choice('messages.errors.exists', $model), ['details' => $details]);
                return 1;
            }
            break;
    }
    return 0;
}

/**
 * pLink: returns a URL string to a profile on the registration ID
 *
 * @param $regID
 * @param $personID
 * @return string
 */
function plink($regID, $personID){
    return '<a href="' . env('APP_URL') . '/profile/' . $personID . '">' . $regID . "</a>";
}

/**
 * translate: array_map function to apply a trans_choice if a translation exists for the term
 */
function et_translate($term){
    $x = 'messages.event_types.';
    if(Lang::has($x.$term)){
        return trans_choice($x.$term, 1);
    } else {
        return $term;
    }
}