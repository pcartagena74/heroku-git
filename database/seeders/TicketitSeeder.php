<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Kordy\Ticketit\Helpers\LaravelVersion;
use Kordy\Ticketit\Models\Setting;

class TicketitSeeder extends Seeder
{
    public $config = [];

    public $update_setting = [
        'routes' => '/app/routes/ticketit.php',
        'email.footer' => 'Powered by mCentric',
        'master_template' => 'v1.auth_pages.page-tmp',
        'email.signoff' => 'Thank you for your patience.',
        'agent_restrict' => 'no',
        'email.footer_link' => 'https://www.mcentric.org',
    ];

    public $categories = [
        'Bug Report' => '#FF0000',
        'Enhancement Request' => '#0000FF',
        'Account Inquiry' => '#008000',
        'Other Question' => '#000000',
    ];

    public $statuses = [
        'New' => 'Gray',
        'In Progress' => 'Green',
        'On Hold' => '#ff8040',
        'Ready for review' => '#008000',
    ];

    public $priorities = [
        'High' => 'Red',
        'Medium' => 'Orange',
        'Low' => 'Green',
    ];

    /**
     * Seed the Plans table.
     */
    public function run(): void
    {
        $defaults = [];

        \Kordy\Ticketit\Models\Status::truncate();
        \Kordy\Ticketit\Models\Category::truncate();
        \Kordy\Ticketit\Models\Priority::truncate();
        Setting::truncate();
        // create tickets statuses
        foreach ($this->statuses as $name => $color) {
            $status = \Kordy\Ticketit\Models\Status::create([
                'name' => $name,
                'color' => $color,
            ]);
        }

        $counter = 0;
        // create tickets statuses
        foreach ($this->categories as $name => $color) {
            $category = \Kordy\Ticketit\Models\Category::create([
                'name' => $name,
                'color' => $color,
            ]);
            $counter++;
        }

        // create tickets statuses
        foreach ($this->priorities as $name => $color) {
            $priority = \Kordy\Ticketit\Models\Priority::create([
                'name' => $name,
                'color' => $color,
            ]);
        }

        $defaults = $this->cleanupAndMerge($this->getDefaults(), $this->config);

        foreach ($defaults as $slug => $column) {
            $setting = Setting::bySlug($slug);
            if ($setting->count()) {
                $setting->first()->update([
                    'default' => $column,
                ]);
            } else {
                $col_value = $column;
                if (isset($this->update_setting[$slug])) {
                    $col_value = $this->update_setting[$slug];
                }
                Setting::create([
                    'lang' => null,
                    'slug' => $slug,
                    'value' => $col_value,
                    'default' => $column,
                ]);
            }
        }
    }

    /**
     * Takes config/ticketit.php, merge with package defaults, and returns serialized array.
     *
     *
     * @return array
     */
    public function cleanupAndMerge($defaults, $config): array
    {
        $merged = array_merge($defaults, $config);

        foreach ($merged as $slug => $column) {
            if (is_array($column)) {
                foreach ($column as $key => $value) {
                    if ($value == 'yes') {
                        $merged[$slug][$key] = true;
                    }

                    if ($value == 'no') {
                        $merged[$slug][$key] = false;
                    }
                }

                $merged[$slug] = serialize($merged[$slug]);
            }

            if ($column == 'yes') {
                $merged[$slug] = true;
            }

            if ($column == 'no') {
                $merged[$slug] = false;
            }
        }

        return (array) $merged;
    }

    public function getDefaults()
    {
        return [

            /*
             * Ticketit main route: Where to load the ticket system (ex. http://url/tickets)
             * Default: /ticket
             */
            'main_route' => 'tickets',
            'main_route_path' => 'tickets',
            /*
             * Ticketit admin route: Where to load the ticket administration dashboard (ex. http://url/tickets-admin)
             * Default: /ticket
             */
            'admin_route' => 'tickets-admin',
            'admin_route_path' => 'tickets-admin',
            /*
             * Template adherence: The master blade template to be extended
             * Default: resources/views/master.blade.php
             */
            'master_template' => 'master',

            /*
             * Switches between bootstrap 3 and 4
             * Default: 3 for <L5.6, 4 for >=L5.6
             */
            'bootstrap_version' => LaravelVersion::min('5.6') ? '4' : '3',

            /*
             * Template adherence: The email blade template to be extended
             * Default: ticketit::emails.templates.ticketit
             */
            'email.template' => 'ticketit::emails.templates.ticketit',
            // resources/views/emails/templates/ticketit.blade.php
            'email.header' => 'Ticket Update',
            'email.signoff' => 'Thank you for your patience!',
            'email.signature' => 'Your friends',
            'email.dashboard' => 'My Dashboard',
            'email.google_plus_link' => '#', // Toogle icon link: false or string
            'email.facebook_link' => '#', // Toogle icon link: false or string
            'email.twitter_link' => '#', // Toogle icon link: false or string
            'email.footer' => 'Powered by Ticketit',
            'email.footer_link' => 'https://github.com/thekordy/ticketit',
            'email.color_body_bg' => '#FFFFFF',
            'email.color_header_bg' => '#44B7B7',
            'email.color_content_bg' => '#F46B45',
            'email.color_footer_bg' => '#414141',
            'email.color_button_bg' => '#AC4D2F',
            /*
             * The default status for new created tickets
             * Default: 1
             */
            'default_status_id' => 1,
            /*
             * The default closing status
             * Default: false
             */
            'default_close_status_id' => false,
            /*
             * The default reopening status
             * Default: false
             */
            'default_reopen_status_id' => false,
            /*
             * [Deprecated] User ids who are members of admin role
             * Default: 1
             */
            //            'admin_ids' => [1],
            /*
             * Pagination length: For standard pagination.
             * Default: 1
             */
            'paginate_items' => 10,
            /*
             * Pagination length: For tickets table.
             * Default: 1
             */
            'length_menu' => [[10, 50, 100], [10, 50, 100]],
            /*
             * Status notification: send email notification to ticket owner/Agent when ticket status is changed
             * Default is send notification: 'yes'
             * Do not send notification: 'no'
             */
            'status_notification' => 'yes',
            /*
             * Comment notification: Send notification when new comment is posted
             * Default is send notification: 'yes'
             * Do not send notification: 'no'
             */
            'comment_notification' => 'yes',
            /*
             * Use Queue method when sending emails (Mail::queue instead of Mail::send). Note that Mail::queue needs to be
             * configured first http://laravel.com/docs/5.1/queues
             * Default is to not use queue: 'no'
             * use queue: 'yes'
             */
            'queue_emails' => 'no',
            /*
             * Agent notify: To notify assigned agent (either auto or manual assignment) of new assigned or transferred tickets
             * Default: 'yes'
             * not to notify agent: 'no'
             */
            'assigned_notification' => 'yes',
            /*
             * Agent restrict: Restrict agents access to only their assigned tickets
             * Default: 'no'
             * Agent access only assigned tickets: 'yes'
             */
            'agent_restrict' => 'no',
            /*
             * Close Ticket Perm: Who has a permission to close tickets
             * Default: ['owner' => 'yes', 'agent' => 'yes', 'admin' => 'yes']
             */
            'close_ticket_perm' => ['owner' => 'yes', 'agent' => 'yes', 'admin' => 'yes'],
            /*
             * Reopen Ticket Perm: Who has a permission to reopen tickets
             * Default: ['owner' => 'yes', 'agent' => 'yes', 'admin' => 'yes']
             */
            'reopen_ticket_perm' => ['owner' => 'yes', 'agent' => 'yes', 'admin' => 'yes'],
            /*
             * Delete Confirmation: Choose which confirmation message type to use when confirming a deleting
             * Default: builtin
             * Options: builtin, modal
             */
            'delete_modal_type' => 'builtin',

            /* ------------------ JS EDITOR ------------------ */

            /*
             * Enable summernote editor on textareas
             * Default: yes
             */
            'editor_enabled' => 'yes',

            /*
             * If Font-awesome css is included outside ticketit, this should be set to 'no'
             * Default: 'yes'
             */
            'include_font_awesome' => 'yes',

            /*
             * Which language should summernote js texteditor use
             * If value is 'laravel', locale set in config/app.php will be used
             *
             * Example: 'hu-HU' for Hungarian
             *
             * See available language codes here: https://cdnjs.com/libraries/summernote/0.7.3
             *
             * Default: 'en'
             */
            'summernote_locale' => 'en',

            /*
             * Whether include codemirror sytax highlighter or not
             * http://summernote.org/examples/#codemirror-as-codeview
             *
             * Default: 'yes'
             */

            'editor_html_highlighter' => 'yes',

            /*
             * Theme for sytax highlighter
             *
             * Available themes here: https://cdnjs.com/libraries/codemirror/5.10.0
             *
             * Default: 'monikai'
             */
            'codemirror_theme' => 'monokai',

            /*
             * Init values for summernote js texteditor in JSON
             * See avaiable options here: http://summernote.org/deep-dive/#initialization-options
             *
             * This setting stores the path to the json config file, relative to project route
             */
            'summernote_options_json_file' => 'vendor/kordy/ticketit/src/JSON/summernote_init.json',

            /*
             * Set which html tags are allowed
             *
             * This overrides the settings part of this file: https://github.com/mewebstudio/Purifier/blob/master/config/purifier.php
             * The same config can be achived by running php artisan vendor:publish and modifying config/purifier.php
             *
             * Full docs: http://htmlpurifier.org/docs
             */

            'purifier_config' => [
                'HTML.SafeIframe' => 'true',
                'URI.SafeIframeRegexp' => '%^(http://|https://|//)(www.youtube.com/embed/|player.vimeo.com/video/)%',
                'URI.AllowedSchemes' => ['data' => true, 'http' => true, 'https' => true, 'mailto' => true, 'ftp' => true],
            ],

            /*
             * Set custom routes file
             *
             * Useful if you want to replace any of the Ticketit components
             *
             * Default: __DIR__.'/routes.php'
             */
            'routes' => base_path('vendor/kordy/ticketit/src').'/routes.php',

        ];
    }
}
