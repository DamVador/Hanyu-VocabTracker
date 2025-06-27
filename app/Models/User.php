<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable //implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function words()
    {
        return $this->hasMany(Word::class);
    }

    /**
     * The roles that belong to the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class); // By default, Laravel looks for 'role_user' pivot table
    }

    /**
     * Check if the user has a specific role.
     *
     * @param string|array $role
     * @return bool
     */
    public function hasRole($role)
    {
        // If a string is passed, convert to array for consistent logic
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }

        // If an array of role names is passed, check if user has any of them
        if (is_array($role)) {
            foreach ($role as $r) {
                if ($this->hasRole($r)) {
                    return true;
                }
            }
            return false;
        }

        return false;
    }

    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    /**
     * Assign a role to the user.
     *
     * @param string $roleName
     * @return void
     */
    public function assignRole(string $roleName)
    {
        $role = Role::where('name', $roleName)->first();
        if ($role && !$this->hasRole($roleName)) {
            $this->roles()->attach($role);
        }
    }

    /**
     * Remove a role from the user.
     *
     * @param string $roleName
     * @return void
     */
    public function removeRole(string $roleName)
    {
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $this->roles()->detach($role);
        }
    }
}
