<?php
/**
 * Comment: Created to support a Twitter Stream for Events if desired
 * Created: 4/1/2017
 */

namespace App\Models;

use App\Jobs\ProcessTweet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\DispatchesJobs;

//use OauthPhirehose;

class TwitterStream // extends OauthPhirehose
{
    use DispatchesJobs;

    /**
     * Enqueue each status
     *
     * @param string $status
     */
    public function enqueueStatus($status)
    {
        $this->dispatch(new ProcessTweet($status));
    }
}
