<?php

namespace App\Utils;

use App\Models\Austritt;
use App\Models\Eroeffnung;
use App\Models\Mutation;

class AntragHelper
{
    public static function hasValidAdUser($user): bool
    {
        return $user && $user->adUser;
    }

    protected static function baseChecks($antrag, $user): array
    {
        $messages = [];
        $canEdit  = true;

        if (! self::hasValidAdUser($user)) 
		{
            $messages[] = [
                "type" => "danger",
                "text" => "Du hast keinen gültigen AD-Benutzer und kannst diesen Antrag nicht bearbeiten.",
            ];
			
            $canEdit = false;
        }

        if (isset($antrag->archiviert) && $antrag->archiviert) 
		{
            $messages[] = [
                "type" => "info",
                "text" => "Dieser Antrag ist archiviert und wird nur im Lesemodus angezeigt.",
            ];
			
            $canEdit = false;
        }

        return [$canEdit, $messages];
    }

	public static function statusForVerarbeitung($antrag, $user): array
	{
		[$canEdit, $messages] = self::baseChecks($antrag, $user);

		if (!$canEdit) 
		{
			return [
				"canEdit"  => false,
				"messages" => $messages,
			];
		}

		if ($antrag->owner_id !== ($user?->adUser?->id)) 
		{
			$messages[] = [
				"type" => "warning",
				"text" => "Du musst Besitzer dieses Antrags sein, um die Aufgaben zu bearbeiten.",
			];
			$canEdit = false;
		}

		return [
			"canEdit"  => $canEdit,
			"messages" => $messages,
		];
	}


	public static function statusForBearbeitung($antrag, $user): array
	{
		[$canEdit, $messages] = self::baseChecks($antrag, $user);

		if (isset($antrag->archiviert) && $antrag->archiviert) 
		{
			return [
				"canEdit"  => false,
				"messages" => $messages,
			];
		}

		$status = $antrag->status;

		if ($status === 2) // Bearbeitung
		{
			if ($user?->hasRole("admin")) 
			{
				$messages[] = [
					"type" => "warning",
					"text" => "Dieser Antrag wird bereits bearbeitet. Als Admin kannst du trotzdem Änderungen vornehmen.",
				];
				
				$canEdit = true;
			} 
			else 
			{
				$messages[] = [
					"type" => "info",
					"text" => "Dieser Antrag wird bereits bearbeitet und darf nicht mehr bearbeitet werden.",
				];
				
				$canEdit = false;
			}
		}

		if ($status === 3) // Abgeschlossen
		{
			$messages[] = [
				"type" => "info",
				"text" => "Dieser Antrag ist abgeschlossen und darf nicht mehr bearbeitet werden.",
			];
			
			$canEdit = false;
		}

		return [
			"canEdit"  => $canEdit,
			"messages" => $messages,
		];
	}

public static function canView($antrag, $user): bool
{
    if ($user?->hasRole("admin")) 
        return true;

    if (!self::hasValidAdUser($user)) 
        return false;

    $adUserId = $user->adUser->id;

    if ($antrag->antragsteller_id === $adUserId) 
        return true;

    $antragstellerUser = \App\Models\User::where('ad_sid', $antrag->antragsteller?->sid)->first();

    if (!$antragstellerUser) 
        return false;

    // Verwende die Relation
    return $antragstellerUser->myRepresentation()->where('ad_users.id', $adUserId)->exists();
}




/**
 * Gibt Status-Badge-Informationen für einen Antrag zurück
 * 
 * @param Eroeffnung|Mutation|Austritt $antrag
 * @return array ['label' => string, 'class' => string, 'code' => int]
 */
public static function getStatusBadge($antrag): array
{
    $statusLabels = [
        1 => ["label" => "Neu",           "class" => "badge bg-secondary py-1"],
        2 => ["label" => "Bearbeitung",   "class" => "badge bg-info py-1"],
        3 => ["label" => "Abgeschlossen", "class" => "badge bg-success py-1"],
    ];
    
    // Berechne den effektiven Status
    $effectiveStatus = self::calculateEffectiveStatus($antrag);
    
    return array_merge(
        $statusLabels[$effectiveStatus] ?? ["label" => "-", "class" => "badge bg-light text-dark py-1"],
        ["code" => $effectiveStatus]
    );
}

/**
 * Berechnet den effektiven Status basierend auf den Einzelstatus
 * 
 * @param Eroeffnung|Mutation|Austritt $antrag
 * @return int 1=Neu, 2=Bearbeitung, 3=Abgeschlossen
 */
protected static function calculateEffectiveStatus($antrag): int
{
    // Wenn status_info gesetzt ist und 2 (abgeschlossen), dann ist alles abgeschlossen
    if (isset($antrag->status_info) && $antrag->status_info == 2) {
        return 3; // Abgeschlossen
    }
    
    // Prüfe ob irgendein Einzelstatus > 1 ist (in Bearbeitung)
    $statusFields = ['status_ad', 'status_tel', 'status_pep', 'status_kis', 'status_sap', 'status_auftrag'];
    
    foreach ($statusFields as $field) {
        if (isset($antrag->$field) && $antrag->$field > 1) {
            return 2; // Bearbeitung
        }
    }
    
    // Standard: Neu
    return 1;
}
	
}
