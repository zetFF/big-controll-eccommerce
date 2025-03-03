<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\LogsActivity;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, LogsActivity;

    protected $activityType = 'user';
    
    public function getActivitySubject(): string
    {
        return "User {$this->name}";
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'status',
        'last_login_at',
        'email_verified_at',
        'settings'
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
            'last_login_at' => 'datetime',
            'settings' => 'array'
        ];
    }

    // Relationships
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function unreadNotificationsCount()
    {
        return $this->notifications()->whereNull('read_at')->count();
    }

    public function wishlist()
    {
        return $this->belongsToMany(Product::class, 'wishlists')
            ->withTimestamps();
    }

    public function hasInWishlist($product)
    {
        return $this->wishlist()
            ->where('product_id', is_object($product) ? $product->id : $product)
            ->exists();
    }

    public function recentlyViewed()
    {
        return $this->belongsToMany(Product::class, 'recently_viewed_products')
            ->withTimestamps()
            ->orderByPivot('created_at', 'desc');
    }

    public function addToRecentlyViewed(Product $product)
    {
        $this->recentlyViewed()->syncWithoutDetaching([$product->id => [
            'created_at' => now(),
            'updated_at' => now()
        ]]);

        // Keep only last 10 products
        $oldProducts = $this->recentlyViewed()
            ->orderByPivot('created_at', 'desc')
            ->skip(10)
            ->pluck('id');

        if ($oldProducts->isNotEmpty()) {
            $this->recentlyViewed()->detach($oldProducts);
        }
    }

    // Helper Methods
    public function hasRole($role): bool
    {
        return $this->roles()->where('slug', $role)->exists();
    }

    public function hasPermission($permission): bool
    {
        return $this->roles()->whereHas('permissions', function($q) use ($permission) {
            $q->where('slug', $permission);
        })->exists();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
}
