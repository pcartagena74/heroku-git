<?php

namespace App\Http\TicketitControllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class ToolsController extends Controller
{
    /**
     * Sorting array of associative arrays - multiple row sorting using a closure.
     * See also: http://the-art-of-web.com/php/sortarray/.
     *
     * @param  array  $data  input-array
     * @return array
     *
     * @internal param array|string $fields array-keys
     *
     * @license Public Domain
     */
    public function sortArray(array $data, $field, string $type = 'desc')
    {
        uasort($data, function ($a, $b) use ($field, $type) {
            if ($a[$field] == $b[$field]) {
                return 0;
            }
            if ($type == 'desc') {
                return $a[$field] < $b[$field] ? 1 : -1;
            }
            if ($type == 'asc') {
                return $a[$field] > $b[$field] ? 1 : -1;
            }
        });

        return $data;
    }

    /**
     * Determine if the current request URL and query string matches a pattern.
     *
     * @param mixed  string
     * @return bool
     */
    public function fullUrlIs($match)
    {
        $url = Request::fullUrl();

        if (Str::is($match, $url)) {
            return true;
        }

        return false;
    }
}
