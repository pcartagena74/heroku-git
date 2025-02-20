<?php

namespace App\Models\Ticketit;

use App\Models\Person;
use App\Models\Ticketit\AgentOver as Agent;
use App\Models\Ticketit\CategoryOver as Category;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Jenssegers\Date\Date;
use Kordy\Ticketit\Models\Ticket;
use Kordy\Ticketit\Traits\ContentEllipse;
use Kordy\Ticketit\Traits\Purifiable;

class TicketOver extends Ticket
{
    use ContentEllipse;
    use Purifiable;

    protected $table = 'ticketit';

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * List of completed tickets.
     */
    public function hasComments(): bool
    {
        return (bool) count($this->comments);
    }

    public function isComplete()
    {
        return (bool) $this->completed_at;
    }

    /**
     * List of completed tickets.
     */
    public function scopeComplete($query): Collection
    {
        //static to allow phil to see all records
        if (auth()->user()->id == 1) {
            return $query->whereNotNull('completed_at');
        } else {
            $is_developer = DB::table('role_user')->select('user_id')
                ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')
                ->where(['roles.name' => 'Developer', 'role_user.user_id' => auth()->user()->id])->get();
            if ($is_developer->count() > 0) {
                return $query->whereNotNull('completed_at');
            } else {
                $person = Person::find(auth()->user()->id);
                $orgId = $person->defaultOrgID;

                return $query->whereNotNull('completed_at')->where('orgId', $orgId);
            }
        }
    }

    /**
     * List of active tickets.
     */
    public function scopeActive($query): Collection
    {
        //static to allow phil to see all records
        if (auth()->user()->id == 1) {
            return $query->whereNull('completed_at');
        } else {
            $is_developer = DB::table('role_user')->select('user_id')
                ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')
                ->where(['roles.name' => 'Developer', 'role_user.user_id' => auth()->user()->id])->get();
            if ($is_developer->count() > 0) {
                return $query->whereNull('completed_at');
            } else {
                $person = Person::find(auth()->user()->id);
                $orgId = $person->defaultOrgID;

                return $query->whereNull('completed_at')->where('orgId', $orgId);
            }
        }
    }

    /**
     * Get Ticket status.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo('Kordy\Ticketit\Models\Status', 'status_id');
    }

    /**
     * Get Ticket priority.
     */
    public function priority(): BelongsTo
    {
        return $this->belongsTo('Kordy\Ticketit\Models\Priority', 'priority_id');
    }

    /**
     * Get Ticket category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo('Kordy\Ticketit\Models\Category', 'category_id');
    }

    /**
     * Get Ticket owner.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Get Ticket agent.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo('Kordy\Ticketit\Models\Agent', 'agent_id');
    }

    /**
     * Get Ticket comments.
     */
    public function comments(): HasMany
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
        return new Date;
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
        } elseif (! $value instanceof \DateTimeInterface) {
            $format = $this->getDateFormat();

            return Date::createFromFormat($format, $value);
        }

        return Date::instance($value);
    }

    /**
     * Get all user tickets.
     *
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
     *
     * @return mixed
     */
    public function scopeAgentUserTickets($query, $id)
    {
        //added admin check for agent
        $user = User::where('id', $id)->get()->first();
        if ($user->hasRole(['Admin'])) {
            return $query->where(function ($subquery) {
                // remove so all admin can see that org tickets
                // $subquery->where('agent_ids', $id)->orWhere('user_id', $id);
            });
        } else {
            $is_developer = DB::table('role_user')->select('user_id')
                ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')
                ->where(['roles.name' => 'Developer', 'role_user.user_id' => $id])->get();
            if ($is_developer->count() > 0) {
                return $query->where(function ($subquery) use ($id) {
                    $subquery->where('user_id', $id)->orwhere('agent_id', $id);
                });
            }

            return $query->where(function ($subquery) use ($id) {
                $subquery->where('user_id', $id);
            });
        }
    }

    /**
     * Sets the agent with the lowest tickets assigned in specific category.
     */
    public function autoSelectAgent($dev = false): Ticket
    {
        $cat_id = $this->category_id;
        $orgId = $this->orgId;
        //removed as to add category with agent it will required changes in add roles and remove roles methods.
        // $agents = Category::find($cat_id)->agents()->with(['agentOpenTickets' => function ($query) use ($orgId) {
        //     $query->addSelect(['id', 'agent_id']);
        //     $query->where('orgId', $orgId);
        // }])->get();
        $agents = [];
        if ($dev == false) {
            // assign to admin only
            $agents = Person::whereIn('personID', function ($q) use ($orgId) {
                $q->select('user_id')
                    ->from('role_user')
                    ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')
                    ->where('roles.name', 'Admin')
                    ->where('roles.orgId', $orgId);
            })->whereNotIn('personID', function ($q) {
                $q->select('user_id')
                    ->from('role_user')
                    ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')
                    ->where('roles.name', '=', 'Developer');
            })->get();
        } else {
            // assign to developer only
            $agents = Person::whereIn('personID', function ($q) {
                $q->select('user_id')
                    ->from('role_user')
                    ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')
                    ->where('roles.name', 'Developer');
            })->whereNotIn('personID', function ($q) use ($orgId) {
                $q->select('user_id')
                    ->from('role_user')
                    ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')
                    ->where('roles.name', '=', 'Admin')
                    ->where('roles.orgId', $orgId);
            })->get();
        }
        $count = 0;
        $lowest_tickets = 1000000;

        // If no agent selected, select the admin
        // as phil is the default admin removed this query from below
        // $first_admin = Agent::admins()->first();
        // $selected_agent_id = $first_admin->id;

        $selected_agent_id = 1;
        foreach ($agents as $agent) {
            if ($count == 0) {
                $lowest_tickets = $this->agentOpenTicketsCount($agent->personID);
                $selected_agent_id = $agent->personID;
            } else {
                $tickets_count = $this->agentOpenTicketsCount($agent->personID);
                if ($tickets_count < $lowest_tickets) {
                    $lowest_tickets = $tickets_count;
                    $selected_agent_id = $agent->personID;
                }
            }
            $count++;
        }
        $this->agent_id = $selected_agent_id;

        return $this;
    }

    public function agentOpenTicketsCount($agent_id)
    {
        return DB::table('ticketit')->where('agent_id', $agent_id)->select('id')->get()->count();
    }
}
