<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Incident",
 *     required={"title", "description", "priority", "metadata"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Beispiel-Incident"),
 *     @OA\Property(property="description", type="string", example="Dies ist eine Beschreibung des Incidents."),
 *     @OA\Property(property="priority", type="string", enum={"high", "medium", "low"}, example="high"),
 *     @OA\Property(property="metadata", type="object", example={"key1": "value1", "key2": "value2"}),
 *     @OA\Property(property="created_by", type="integer", example=1),
 *     @OA\Property(property="resolved_by", type="integer", example=1, nullable=true),
 *     @OA\Property(property="resolved_at", type="string", format="date-time", example="2023-06-08T10:30:00Z", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-06-08T09:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-06-08T09:30:00Z")
 *)
 */
class Incident extends Model
{
    protected $fillable = [
        "title",
        "description",
        "priority",
        "metadata",
        "created_by",
        "resolved_by",
        "resolved_at",
    ];

    protected $casts = [
        "metadata" => "array",
        "resolved_at" => "datetime",
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, "created_by");
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, "resolved_by");
    }

    public function scopeOpen($query)
    {
        return $query->whereNull("resolved_at");
    }

    public function scopeResolved($query)
    {
        return $query->whereNotNull("resolved_at");
    }

    public function isResolved(): bool
    {
        return !is_null($this->resolved_at);
    }

    public function resolve(?int $userId = null): void
    {
        $this->update([
            "resolved_at" => now(),
            "resolved_by" => $userId ?? auth()->id(),
        ]);
    }
}