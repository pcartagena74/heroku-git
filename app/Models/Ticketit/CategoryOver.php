<?php

namespace App\Models\Ticketit;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Kordy\Ticketit\Models\Category as Model;

class CategoryOver extends Model
{
    protected $table = 'ticketit_categories';

    protected $fillable = ['name', 'color'];

    /**
     * Indicates that this model should not be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get related tickets.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(\App\Models\Ticketit\TicketOver::class, 'category_id');
    }

    /**
     * Get related agents.
     */
    public function agents(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Ticketit\AgentOver::class, 'ticketit_categories_users', 'category_id', 'user_id');
    }
}
