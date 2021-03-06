<?php
/**
 * Comment: Created to support a Twitter Stream for Events if desired
 * Created: 4/1/2017
 */

namespace App;

use OauthPhirehose;
use App\Jobs\ProcessTweet;
use Illuminate\Foundation\Bus\DispatchesJobs;

class TwitterStream extends OauthPhirehose
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
