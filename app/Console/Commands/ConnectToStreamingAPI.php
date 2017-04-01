<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\TwitterStream;

class ConnectToStreamingAPI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:twitter-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Opens a twitter stream';

    protected $twitterStream;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(TwitterStream $twitterStream)
    {
        $this->twitterStream = $twitterStream;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $twitter_consumer_key = env('TWITTER_CONSUMER_KEY', '');
        $twitter_consumer_secret = env('TWITTER_CONSUMER_SECRET', '');

        $this->twitterStream->consumerKey = $twitter_consumer_key;
        $this->twitterStream->consumerSecret = $twitter_consumer_secret;
        $this->twitterStream->setTrack(array('scotch_io'));
        $this->twitterStream->consume();
    }
}
