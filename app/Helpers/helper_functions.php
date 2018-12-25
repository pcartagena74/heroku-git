<?php
/**
 * Comment: Added to have some global functions
 * Created: 8/25/2017
 */

use App\Org;
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
    $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_PARSEHUGE);
    //$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'),LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_PARSEHUGE);

    $org = Org::find($orgID);
    $images = $dom->getElementsByTagName('img');

    foreach($images as $img){
        $src = $img->getAttribute('src');

        if(preg_match('/data:image/', $src)){

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
            $img->setAttribute('src', $new_src);
        }
    }
    return $dom->saveHTML();
}