<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Notifications\ResetPassword;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="User",
 *     description="Benutzer Objekt",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="username", type="string", example="maxmuster"),
 *     @OA\Property(property="auth_type", type="string", example="local"),
 *     @OA\Property(property="firstname", type="string", example="Max"),
 *     @OA\Property(property="lastname", type="string", example="Mustermann"),
 *     @OA\Property(property="email", type="string", format="email", example="max@example.com"),
 *     @OA\Property(property="is_enabled", type="boolean", example=true),
 *     @OA\Property(
 *         property="settings",
 *         type="object",
 *         additionalProperties=true,
 *         example={"darkmode_enabled": true}
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-21T12:34:56Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-21T12:34:56Z")
 * )
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        "username",
        "auth_type",
		"ad_sid",
        "firstname",
        "lastname",
        "email",
        "password",
        "is_enabled",
		"settings" => "array",
    ];

    protected $hidden = [
        "password",
        "remember_token",
    ];

    protected function casts(): array
    {
        return [
			"ad_sid" => "string",
			"email_verified_at" => "datetime",
            "password" => "hashed",
            "is_enabled" => "boolean",
			"settings" => "array",
        ];
    }

	public function guardName(): string
	{
		return "web";
	}

    public function getNameAttribute(): string
    {
        return "{$this->firstname} {$this->lastname}";
    }

    public function isLdap(): bool
    {
        return $this->auth_type === "ldap";
    }

	public function getSetting(string $key, $default = null)
	{
		return $this->settings[$key] ?? $default;
	}

	public function setSetting(string $key, $value): void
	{
		$settings = $this->settings ?? [];
		$settings[$key] = $value;
		$this->settings = $settings;
		$this->save();
	}

	public function sendPasswordResetNotification($token): void
	{
		$this->notify(new ResetPassword($token));
	}

	protected static function booted()
	{
		static::deleting(function ($user) {
			$user->syncRoles([]);
			$user->syncPermissions([]);
		});
	}

	public function adUser()
	{
		return $this->hasOne(AdUser::class, "sid", "ad_sid");
	}
}
