<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'clerk_id',
        'credits',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        //
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'credits' => 'integer',
        ];
    }

    /**
     * Find user by Clerk ID
     */
    public static function findByClerkId(string $clerkId): ?User
    {
        return static::where('clerk_id', $clerkId)->first();
    }

    /**
     * Create or update user from Clerk JWT claims
     */
    public static function createOrUpdateFromClerk(array $claims): User
    {
        return static::updateOrCreate(
            ['clerk_id' => $claims['sub']],
            [
                'name' => $claims['name'] ?? '',
                'email' => $claims['email'] ?? '',
                'clerk_id' => $claims['sub'],
            ]
        );
    }
}
