<?php

namespace App\Models\Ticketit;

use App\Models\Ticketit\AgentOver as Agent;
use App\Models\Ticketit\CategoryOver as Category;
use App\Person;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Jenssegers\Date\Date;
use Kordy\Ticketit\Models\Ticket;
use Kordy\Ticketit\Traits\ContentEllipse;
use Kordy\Ticketit\Traits\Purifiable;

class TicketOver extends Ticket
{
    use ContentEllipse;
    use Purifiable;

    protected $table = 'ticketit';
    protected $dates = ['completed_at'];

    /**
     * List of completed tickets.
     *
     * @return bool
     */
    public function hasComments()
    {
        return (bool) count($this->comments);
    }

    public function isComplete()
    {
        return (bool) $this->completed_at;
    }

    /**
     * List of completed tickets.
     *
     * @return Collection
     */
    public function scopeComplete($query)
    {
        //static to allow phil to see all records
        if (auth()->user()->id == 1) {
            return $query->whereNotNull('completed_at');
        } else {
            $person = Person::find(auth()->user()->id);
            $orgId  = $person->defaultOrgID;
            return $query->whereNotNull('completed_at')->where('orgId', $orgId);
        }
    }

    /**
     * List of active tickets.
     *
     * @return Collection
     */
    public function scopeActive($query)
    {
        //static to allow phil to see all records
        if (auth()->user()->id == 1) {
            return $query->whereNull('completed_at');
        } else {
            $person = Person::find(auth()->user()->id);
            $orgId  = $person->defaultOrgID;
            return $query->whereNull('completed_at')->where('orgId', $orgId);

        }
    }

    /**
     * Get Ticket status.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status()
    {
        return $this->belongsTo('Kordy\Ticketit\Models\Status', 'status_id');
    }

    /**
     * Get Ticket priority.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function priority()
    {
        return $this->belongsTo('Kordy\Ticketit\Models\Priority', 'priority_id');
    }

    /**
     * Get Ticket category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo('Kordy\Ticketit\Models\Category', 'category_id');
    }

    /**
     * Get Ticket owner.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    /**
     * Get Ticket agent.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function agent()
    {
        return $this->belongsTo('Kordy\Ticketit\Models\Agent', 'agent_id');
    }

    /**
     * Get Ticket comments.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany('Kordy\Ticketit\Models\Comment', 'ticket_id');
    }

//    /**
    //     * Get Ticket audits
    //     *
    //     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    //     */
    //    public function audits()
    //    {
    //        return $this->hasMany('Kordy\Ticketit\Models\Audit', 'ticket_id');
    //    }
    //

    /**
     * @see Illuminate/Database/Eloquent/Model::asDateTime
     */
    public function freshTimestamp()
    {
        return new Date();
    }

    /**
     * @see Illuminate/Database/Eloquent/Model::asDateTime
     */
    protected function asDateTime($value)
    {
        if (is_numeric($value)) {
            return Date::createFromTimestamp($value);
        } elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value)) {
            return Date::createFromFormat('Y-m-d', $value)->startOfDay();
        } elseif (!$value instanceof \DateTimeInterface) {
            $format = $this->getDateFormat();

            return Date::createFromFormat($format, $value);
        }

        return Date::instance($value);
    }

    /**
     * Get all user tickets.
     *
     * @param $query
     * @param $id
     *
     * @return mixed
     */
    public function scopeUserTickets($query, $id)
    {
        return $query->where('user_id', $id);
    }

    /**
     * Get all agent tickets.
     *
     * @param $query
     * @param $id
     *
     * @return mixed
     */
    public function scopeAgentTickets($query, $id)
    {
        return $query->where('agent_id', $id);
    }

    /**
     * Get all agent tickets.
     *
     * @param $query
     * @param $id
     *
     * @return mixed
     */
    public function scopeAgentUserTickets($query, $id)
    {
        //added admin check for agent
        $user = User::where('id',$id)->get()->first();
        if ($user->hasRole(['Admin'])) {
            return $query->where(function ($subquery) use ($id) {
                $subquery->where('agent_id', $id)->orWhere('user_id', $id);
            });
        } else {
            return $query->where(function ($subquery) use ($id) {
                $subquery->where('user_id', $id);
            });
        }
    }

    /**
     * Sets the agent with the lowest tickets assigned in specific category.
     *
     * @return Ticket
     */
    public function autoSelectAgent()
    {
        $cat_id = $this->category_id;
        $orgId  = $this->orgId;
        $agents = Category::find($cat_id)->agents()->with(['agentOpenTickets' => function ($query) use ($orgId) {
            $query->addSelect(['id', 'agent_id']);
            $query->where('orgId', $orgId);
        }])->get();
        $count          = 0;
        $lowest_tickets = 1000000;
        // If no agent selected, select the admin
        $first_admin = Agent::admins()->first();

        $selected_agent_id = $first_admin->id;
        foreach ($agents as $agent) {
            if ($count == 0) {
                $lowest_tickets    = $agent->agentOpenTickets->count();
                $selected_agent_id = $agent->id;
            } else {
                $tickets_count = $agent->agentOpenTickets->count();
                if ($tickets_count < $lowest_tickets) {
                    $lowest_tickets    = $tickets_count;
                    $selected_agent_id = $agent->id;
                }
            }
            $count++;
        }
        $this->agent_id = $selected_agent_id;

        return $this;
    }
}
