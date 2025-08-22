<?php

namespace DevsFort\Pigeon\Chat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    protected $fillable = [
        'name',
        'description',
        'avatar',
        'created_by',
        'is_private'
    ];

    protected $casts = [
        'is_private' => 'boolean',
    ];

    /**
     * Get the user who created the group
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get all members of the group
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\User::class, 'group_members', 'group_id', 'user_id')
                    ->withPivot('role', 'is_active', 'joined_at')
                    ->withTimestamps();
    }

    /**
     * Get all messages in the group
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'to_id')->where('type', 'group');
    }

    /**
     * Check if a user is a member of the group
     */
    public function isMember($userId): bool
    {
        return $this->members()->where('user_id', $userId)->exists();
    }

    /**
     * Check if a user is an admin of the group
     */
    public function isAdmin($userId): bool
    {
        return $this->members()->where('user_id', $userId)->wherePivot('role', 'admin')->exists();
    }

    /**
     * Get the group avatar URL
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset('/storage/' . config('devschat.group_avatar.folder', 'groups') . '/' . $this->avatar);
        }
        return asset('/storage/' . config('devschat.group_avatar.folder', 'groups') . '/default-group.png');
    }
}
