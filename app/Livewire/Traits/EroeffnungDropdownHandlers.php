<?php

namespace App\Livewire\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Helper Formular
 */
trait EroeffnungDropdownHandlers
{
     // Lädt generische Dropdowns (z. B. Arbeitsort, Abteilung, Funktion, SAP-Rolle)
	protected function loadDropdown(string $modelClass, array|int|null $extraIds, string $targetProperty, string $labelField = 'name', string $enabledField = 'enabled', ?callable $scope = null): void 
	{
		$extraIds = collect($extraIds)->filter()->unique()->values()->toArray();

		// Aktive Einträge laden (nach optionalem Scope)
		$activeQuery = $modelClass::query()->orderBy($labelField);
		
		if (!$this->neue_konstellation && $scope) 
		{
			$activeQuery = $scope($activeQuery);
		}

		if ($this->neue_konstellation || $this->isCreate) 
		{
			$activeQuery->where($enabledField, true);
		} 
		else 
		{
			$activeQuery->where($enabledField, true);
		}

		$active = $activeQuery->get(['id', $labelField]);

		// Gespeicherte Einträge nachladen (ohne Filter)
		$extras = collect();
		
		if (!empty($extraIds)) 
		{
			$extras = $modelClass::query()
				->whereIn('id', $extraIds)
				->get(['id', $labelField]);
		}

		// Zusammenführen
		$records = $extras->merge($active)->unique('id');

		// Ergebnis mappen
		$this->{$targetProperty} = $records
			->map(fn($item) => [
				'id' => $item->id,
				'name' => $item->{$labelField},
			])
			->toArray();
	}

    // Lädt Dropdown für AD-Benutzer (Bezugsperson, Vorlage, Kalender)
	protected function loadAdUserDropdown(array|int|null $extraIds, string $targetProperty, string $enabledField = 'is_enabled'): void 
	{
		$extraIds = collect($extraIds)->filter()->unique()->values()->toArray();

		// Aktive Benutzer
		$activeQuery = \App\Models\AdUser::query()
			->with('funktion')
			->orderBy('display_name');

		if ($this->neue_konstellation || $this->isCreate) 
		{
			$activeQuery->where('is_existing', true)->where($enabledField, true);
		} 
		else 
		{
			if ($this->filter_mitarbeiter && $this->abteilung_id && $targetProperty !== 'adusersKalender') 
			{
				$activeQuery->where('abteilung_id', $this->abteilung_id);
			}
			
			$activeQuery->where('is_existing', true)->where($enabledField, true);
		}

		$active = $activeQuery->get();

		// Gespeicherte Benutzer (ohne Filter)
		$extras = collect();
		
		if (!empty($extraIds)) 
		{
			$extras = \App\Models\AdUser::with('funktion')
				->whereIn('id', $extraIds)
				->get();
		}

		// Merge + unique
		$records = $extras->merge($active)->unique('id');

		// Mapping
		$this->{$targetProperty} = $records
			->map(fn($user) => [
				'id' => $user->id,
				'display_name' => \Illuminate\Support\Str::limit(
					$user->funktion
						? $user->display_name . ' (' . $user->funktion->name . ')'
						: $user->display_name,
					40 // Max. Zeichen
				),
			])
			->toArray();
	}
}
