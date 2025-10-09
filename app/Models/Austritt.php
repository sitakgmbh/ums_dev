<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Austritt",
 *     type="object",
 *     title="Austritt",
 *     description="Austritt Objekt",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="owner_id", type="integer", nullable=true, example=12, description="ID des Besitzers (AdUser)"),
 *     @OA\Property(property="ad_user_id", type="integer", example=53, description="Verknüpfter AD-User"),
 *     @OA\Property(property="vertragsende", type="string", format="date", example="2025-10-17"),
 *     @OA\Property(property="ticket_nr", type="string", nullable=true, example="TCK-123456"),
 *     @OA\Property(property="status_pep", type="boolean", example=true),
 *     @OA\Property(property="status_kis", type="boolean", example=false),
 *     @OA\Property(property="status_streamline", type="boolean", example=true),
 *     @OA\Property(property="status_tel", type="boolean", example=false),
 *     @OA\Property(property="status_alarmierung", type="boolean", example=false),
 *     @OA\Property(property="status_logimen", type="boolean", example=true),
 *     @OA\Property(property="archiviert", type="boolean", example=false),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-21T12:34:56Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-21T12:34:56Z")
 * )
 */
class Austritt extends Model
{
    use HasFactory;

    protected $table = "austritte";

    protected $fillable = [
        "owner_id",
        "vertragsende",
        "ad_user_id",
        "status_pep",
        "status_kis",
        "status_streamline",
        "status_tel",
        "status_alarmierung",
        "status_logimen",
        "ticket_nr",
        "archiviert",
    ];

    protected $casts = [
        "vertragsende"      => "date:Y-m-d",
        "archiviert"        => "boolean",
    ];

    public function owner()
    {
        return $this->belongsTo(AdUser::class, "owner_id");
    }

    public function adUser()
    {
        return $this->belongsTo(AdUser::class, "ad_user_id");
    }

	public function getStatusAttribute(): int
	{
		if ($this->status_info === 2) return 3; // 3 = Abgeschlossen
		$max = max($this->status_pep, $this->status_kis, $this->status_streamline, $this->status_tel, $this->status_alarmierung, $this->status_logimen);
		return $max > 1 ? 2 : 1; // grösser als 1 = Bearbeitung sonst Neu
	}
}
