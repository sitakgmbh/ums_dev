<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use App\Utils\Logging\Logger;
use App\Models\User;

class UserObserver
{
    protected function filterData(array $data, User $user): array
    {
        $sensitive = ["password", "remember_token"];

        return collect($data)
            ->only($user->getFillable())
            ->except($sensitive)
            ->toArray();
    }

    public function created(User $user): void
    {
        $actor = Auth::user();
        $actorUsername = $actor?->username ?? "system";
        $actorFullname = $actor?->name ?? ($actor?->firstname." ".$actor?->lastname);

        $context = [
            "actor_id"   => $actor?->id,
            "actor_user" => $actorUsername,
            "fullname"   => $actorFullname,
            "username"   => $user->username,
            "data"       => $this->filterData($user->getAttributes(), $user),
        ];

        Logger::db("system", "info", "Benutzer {$user->username} erstellt durch {$actorFullname} ({$actorUsername})", $context);
    }

    public function updated(User $user): void
    {
        $actor = Auth::user();
        $actorUsername = $actor?->username ?? "system";
        $actorFullname = $actor?->name ?? ($actor?->firstname." ".$actor?->lastname);

        $changes  = $this->filterData($user->getChanges(), $user);
        $original = $this->filterData($user->getOriginal(), $user);

        Logger::db("system", "info", "Benutzer {$user->username} bearbeitet durch {$actorFullname} ({$actorUsername})", [
            "actor_id"   => $actor?->id,
            "actor_user" => $actorUsername,
            "fullname"   => $actorFullname,
            "username"   => $user->username,
            "changes"    => $changes,
            "original"   => $original,
        ]);
    }

    public function deleted(User $user): void
    {
        $actor = Auth::user();
        $actorUsername = $actor?->username ?? "system";
        $actorFullname = $actor?->name ?? ($actor?->firstname." ".$actor?->lastname);

        $deletedData = $this->filterData($user->getOriginal(), $user);

        Logger::db("system", "info", "Benutzer {$user->username} gelöscht durch {$actorFullname} ({$actorUsername})", [
            "actor_id"     => $actor?->id,
            "actor_user"   => $actorUsername,
            "fullname"     => $actorFullname,
            "username"     => $user->username,
            "deleted_data" => $deletedData,
        ]);
    }
}
