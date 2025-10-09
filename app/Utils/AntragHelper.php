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
				
				$canEdit = true; // Admin = OK
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
		{
			return true;
		}

		if (! self::hasValidAdUser($user)) 
		{
			return false;
		}

		return $antrag->antragsteller_id === $user->adUser->id; // nur eigene Anträge
	}
}
