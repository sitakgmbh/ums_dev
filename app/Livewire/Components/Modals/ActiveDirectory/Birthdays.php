<?php

namespace App\Livewire\Components\Modals\ActiveDirectory;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\AdUser;
use Carbon\Carbon;

class Birthdays extends BaseModal
{
    public $activeFilter = 'today';
    public $data = [];

    public $filters = [
        'today',
        'thisWeek',
        'thisMonth',
    ];

    protected function openWith(array $payload): bool
    {
        $this->loadData();

        $this->title = "🎂 Geburtstage";
        $this->size = "md";
        $this->position = "centered";
        $this->backdrop = false;
        $this->headerBg = "bg-primary";
        $this->headerText = "text-white";

        return true;
    }

    public function setFilter(string $filter): void
    {
        $this->activeFilter = $filter;
        $this->loadData();
    }

	protected function loadData(): void
	{
		$today = \Carbon\Carbon::today();

		$users = AdUser::query()
			->where('is_existing', true)
			->where('is_enabled', true)
			->whereNotNull('extensionattribute2')
			->get();

		$this->data = $users
			->map(function ($user) use ($today) {
				try 
				{
					$birthday = \Carbon\Carbon::parse($user->extensionattribute2);

					// Geburtstag im aktuellen Jahr
					$nextBirthday = $birthday->copy()->setYear($today->year);

					// Falls dieses Jahr bereits vorbei nächstes Jahr
					if ($nextBirthday->lt($today)) 
					{
						$nextBirthday->addYear();
					}

					$user->next_birthday = $nextBirthday;
					$user->is_today = $nextBirthday->isSameDay($today);
					$user->days_until = $today->diffInDays($nextBirthday);

					return $user;
				} 
				catch (\Throwable $e) 
				{
					return null;
				}
			})
			->filter()
			->when($this->activeFilter === 'today', function ($collection) {
				return $collection->filter(fn ($u) => $u->is_today);
			})
			->when($this->activeFilter === 'thisWeek', function ($collection) {
				return $collection->filter(fn ($u) =>
					$u->days_until >= 1 && $u->days_until <= 7
				);
			})
			->sortBy([
				['next_birthday', 'asc'],
				['display_name', 'asc'],
			])
			->values();
	}

    public function render()
    {
        return view('livewire.components.modals.active-directory.birthdays');
    }
}
