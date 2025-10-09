<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use App\Utils\Logging\Logger;
use App\Models\AdUser;

class AdUserObserver
{
    public function created(AdUser $user): void
    {
        $actor = Auth::user();
        $actorUsername = $actor?->username ?? "system";
        $actorFullname = $actor?->name ?? ($actor?->firstname." ".$actor?->lastname);

        Logger::db("sap", "info", "AD-Benutzer {$user->username} erstellt", [
            "actor_id"   => $actor?->id,
            "actor_user" => $actorUsername,
            "fullname"   => $actorFullname,
            "username"   => $user->username,
            "data"       => $user->getAttributes(),
        ]);
    }

    public function updated(AdUser $user): void
    {
        $actor = Auth::user();
        $actorUsername = $actor?->username ?? "system";
        $actorFullname = $actor?->name ?? ($actor?->firstname." ".$actor?->lastname);

        Logger::db("sap", "info", "AD-Benutzer {$user->username} bearbeitet", [
            "actor_id"   => $actor?->id,
            "actor_user" => $actorUsername,
            "fullname"   => $actorFullname,
            "username"   => $user->username,
            "changes"    => $user->getChanges(),
            "original"   => $user->getOriginal(),
        ]);
    }

    public function deleted(AdUser $user): void
    {
        $actor = Auth::user();
        $actorUsername = $actor?->username ?? "system";
        $actorFullname = $actor?->name ?? ($actor?->firstname." ".$actor?->lastname);

        Logger::db("sap", "info", "AD-Benutzer {$user->username} gelöscht", [
            "actor_id"     => $actor?->id,
            "actor_user"   => $actorUsername,
            "fullname"     => $actorFullname,
            "username"     => $user->username,
            "deleted_data" => $user->getOriginal(),
        ]);
    }
}
