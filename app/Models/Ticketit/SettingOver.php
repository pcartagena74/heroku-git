<?php

namespace App\Models\Ticketit;

use Cache;
use Illuminate\Database\Eloquent\Model;
use Kordy\Ticketit\Helpers\LaravelVersion;
use Kordy\Ticketit\Models\SetingOver as Setting;
use Kordy\Ticketit\Models\Setting as Table;

class SettingOver extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['lang', 'slug', 'value', 'default'];

    /**
     * @var string
     */
    protected $table = 'ticketit_settings';

    /**
     * Returns one of three columns by slug.
     * Priority: lang, value, default.
     *
     *
     * @return mixed
     */
    public function scopeBySlug($query, $slug)
    {
        return $query->whereSlug($slug);
    }

    /**
     * Grab a setting from cached Settings table by slug.
     * Cache lifetime: 60 minutes.
     *
     *
     * @return mixed
     */
    public static function grab($slug)
    {
        /*
         * Comment out prior to 0.2 launch. Will cause massive amount
         * of Database queries. Only for adding new settings while
         * in development and testing.
         */
        //       Cache::flush();

        // seconds expected for L5.8<=, minutes before that
        $time = LaravelVersion::min('5.8') ? 60 * 60 : 60;

        $setting = Cache::remember('ticketit::settings.' . $slug, $time, function () use ($slug, $time) {
            $settings = Cache::remember('ticketit::settings', $time, function () {
                return Table::all();
            });

            $setting = $settings->where('slug', $slug)->first();

            if ($setting->lang) {
                return trans($setting->lang);
            }

            if (self::is_serialized($setting->value)) {
                $value = unserialize($setting->value);
                // Route settings should always be strings
                if (in_array($slug, ['main_route', 'admin_route', 'admin_route_path'])) {
                    return is_array($value) ? (string)reset($value) : (string)$value;
                }
                return $value;
            }

            return $setting;
        });

        return $setting;
    }

    /**
     * Check if a parameter under Value or Default columns
     * is serialized.
     */
    public static function is_serialized($data, $strict = true): bool
    {
        // if it isn't a string, it isn't serialized.
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ($data == 'N;') {
            return true;
        }
        if (strlen($data) < 4) {
            return false;
        }
        if ($data[1] !== ':') {
            return false;
        }
        if ($strict) {
            $lastc = substr($data, -1);
            if ($lastc !== ';' && $lastc !== '}') {
                return false;
            }
        } else {
            $semicolon = strpos($data, ';');
            $brace = strpos($data, '}');
            // Either ; or } must exist.
            if ($semicolon === false && $brace === false) {
                return false;
            }

            // But neither must be in the first X characters.
            if ($semicolon !== false && $semicolon < 3) {
                return false;
            }

            if ($brace !== false && $brace < 4) {
                return false;
            }
        }
        $token = $data[0];
        switch ($token) {
            case 's':
                if ($strict) {
                    if (substr($data, -2, 1) !== '"') {
                        return false;
                    }
                } elseif (strpos($data, '"') === false) {
                    return false;
                }
            // or else fall through
            case 'a':
            case 'O':
                return (bool)preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b':
            case 'i':
            case 'd':
                $end = $strict ? '$' : '';

                return (bool)preg_match("/^{$token}:[0-9.E-]+;$end/", $data);
        }

        return false;
    }
}
