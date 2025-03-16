<?php

namespace App\Models\Ticketit;

use App\Models\Ticketit\TicketOver as Ticketit;
use Auth;
use Entrust;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Kordy\Ticketit\Models\Agent as User;

class AgentOver extends User
{
    protected $table = 'users';

    /**
     * list of all agents and returning collection.
     *
     *
     * @internal param int $cat_id
     */
    public function scopeAgents($query, $paginate = false)
    {
        $user = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['Admin']);
        })->get();
        if ($paginate) {
            return $query->where('ticketit_agent', '1')->paginate($paginate, ['*'], 'agents_page');
        } else {
            return $query->where('ticketit_agent', '1');
        }
    }

    /**
     * list of all admins and returning collection.
     *
     *
     * @internal param int $cat_id
     */
    public function scopeAdmins($query, $paginate = false, $withOrg = false)
    {
        if ($withOrg !== false) {
            return $query->where('ticketit_admin', '1')->paginate($paginate, ['*'], 'admins_page');
        }
        if ($paginate) {
            return $query->where('ticketit_admin', '1')->paginate($paginate, ['*'], 'admins_page');
        } else {
            return $query->where('ticketit_admin', '1')->get();
        }
    }

    /**
     * list of all agents and returning collection.
     *
     *
     * @internal param int $cat_id
     */
    public function scopeUsers($query, $paginate = false)
    {
        if ($paginate) {
            return $query->where('ticketit_agent', '0')->paginate($paginate, ['*'], 'users_page');
        } else {
            return $query->where('ticketit_agent', '0')->get();
        }
    }

    /**
     * list of all agents and returning lists array of id and name.
     *
     *
     *
     * @internal param int $cat_id
     */
    public function scopeAgentsLists($query)
    {
        if (version_compare(app()->version(), '5.2.0', '>=')) {
            return $query->where('ticketit_agent', '1')->pluck('name', 'id')->toArray();
        } else {
            // if Laravel 5.1
            return $query->where('ticketit_agent', '1')->lists('name', 'id')->toArray();
        }
    }

    /**
     * Check if user is agent.
     */
    public static function isAgent($id = null)
    {
        //as we want to have agent who are admin of particular org we can use entrust. we have already
        //updated entrust to check org id for all roles.
        //as per new requirement by Phil Mar 13, 2020
        //admin can assign ticket to developer to do so we need admin and developer both to be agents.
        //developer can belongs to any org
        //agent will only be from same org.
        //so manually quering db as exsiting roles are tied with org id(for developer).

        if (isset($id)) {
            $is_developer = self::checkUserIsDeveloper($id);
            if ($is_developer) {
                return true;
            }
            $user = User::where('id', $id)->get()->first();

            return $user->hasRole(['Admin']);
        }
        if (auth()->check()) {
            $is_developer = self::checkUserIsDeveloper(auth()->user()->id);
            if ($is_developer) {
                return true;
            }

            return auth()->user()->hasRole(['Admin']);
        }

        return false;
    }

    /**
     * Check if user is admin.
     */
    public static function isAdmin()
    {
        return auth()->check() && auth()->user()->ticketit_admin;
    }

    /**
     * Check if user is the assigned agent for a ticket.
     *
     * @param int $id ticket id
     */
    public static function isAssignedAgent($id)
    {
        $is_admin = Entrust::hasRole('Admin');

        return auth()->check() && (($is_admin && Auth::user()->id == TicketOver::find($id)->agent->id) || self::checkUserIsDeveloper(Auth::user()->id));
    }

    /**
     * Check if user is the owner for a ticket.
     *
     * @param int $id ticket id
     */
    public static function isTicketOwner($id)
    {
        $ticket = TicketOver::find($id);

        return $ticket && auth()->check() &&
            auth()->user()->id == $ticket->user->id;
    }

    /**
     * Get related categories.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany('Kordy\Ticketit\Models\Category', 'ticketit_categories_users', 'user_id', 'category_id');
    }

    /**
     * Get related agent tickets (To be deprecated).
     */
    public function agentTickets($complete = false): HasMany
    {
        if ($complete) {
            return $this->hasMany(Ticketit::class, 'agent_id')->whereNotNull('completed_at');
        } else {
            return $this->hasMany(Ticketit::class, 'agent_id')->whereNull('completed_at');
        }
    }

    /**
     * Get related user tickets (To be deprecated).
     */
    public function userTickets($complete = false): HasMany
    {
        if ($complete) {
            return $this->hasMany(Ticketit::class, 'user_id')->whereNotNull('completed_at');
        } else {
            return $this->hasMany(Ticketit::class, 'user_id')->whereNull('completed_at');
        }
    }

    public function tickets($complete = false): \Kordy\Ticketit\Models\HasMany
    {
        if ($complete) {
            return $this->hasMany(Ticketit::class, 'user_id')->whereNotNull('completed_at');
        } else {
            return $this->hasMany(Ticketit::class, 'user_id')->whereNull('completed_at');
        }
    }

    public function allTickets($complete = false) // (To be deprecated)
    {
        if ($complete) {
            return Ticket::whereNotNull('completed_at');
        } else {
            return Ticket::whereNull('completed_at');
        }
    }

    public function getTickets($complete = false) // (To be deprecated)
    {
        $user = self::find(auth()->user()->id);

        if ($user->isAdmin()) {
            $tickets = $user->allTickets($complete);
        } elseif ($user->isAgent()) {
            $tickets = $user->agentTickets($complete);
        } else {
            $tickets = $user->userTickets($complete);
        }

        return $tickets;
    }

    /**
     * Get related agent total tickets.
     */
    public function agentTotalTickets(): HasMany
    {
        return $this->hasMany(Ticketit::class, 'agent_id');
    }

    /**
     * Get related agent Completed tickets.
     */
    public function agentCompleteTickets(): HasMany
    {
        return $this->hasMany(Ticketit::class, 'agent_id')->whereNotNull('completed_at');
    }

    /**
     * Get related agent tickets.
     */
    public function agentOpenTickets(): HasMany
    {
        return $this->hasMany(Ticketit::class, 'agent_id')->whereNull('completed_at');
    }

    /**
     * Get related user total tickets.
     */
    public function userTotalTickets(): HasMany
    {
        return $this->hasMany(Ticketit::class, 'user_id');
    }

    /**
     * Get related user Completed tickets.
     */
    public function userCompleteTickets(): HasMany
    {
        return $this->hasMany(Ticketit::class, 'user_id')->whereNotNull('completed_at');
    }

    /**
     * Get related user tickets.
     */
    public function userOpenTickets(): HasMany
    {
        return $this->hasMany(Ticketit::class, 'user_id')->whereNull('completed_at');
    }

    /**
     * check if given user is a developer
     *
     * @return bool true/false
     */
    protected static function checkUserIsDeveloper(int $user_id)
    {
        $role = DB::table('role_user')
            ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')
            ->where(['roles.name' => 'Developer', 'role_user.user_id' => $user_id])->get();

        return $role->count() > 0;
    }
}
