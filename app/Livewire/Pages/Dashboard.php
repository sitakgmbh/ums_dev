<?php
namespace App\Livewire\Pages;

use App\Models\Eroeffnung;
use App\Models\Mutation;
use App\Models\Austritt;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
/**
 * Zeigt eine Übersicht über die Anzahl eigener Eröffnungen und Mutationen
 * basierend auf der SID des aktuell angemeldeten Benutzers an.
 */
class Dashboard extends Component
{
    public int $eroeffnungenCount = 0;
    public int $mutationenCount = 0;
    public array $wochenUebersicht = [];

    public function mount()
    {
        $userSid = auth()->user()->adUser->sid ?? null;
        
        $this->eroeffnungenCount = Eroeffnung::whereHas("antragsteller", function ($query) use ($userSid) {
            $query->where("sid", $userSid);
        })->count();

        $this->mutationenCount = Mutation::whereHas("antragsteller", function ($query) use ($userSid) {
            $query->where("sid", $userSid);
        })->count();

		if (auth()->user()->hasRole('admin')) {
			$this->wochenUebersicht = $this->getWochenUebersicht();
		}
    }


private function getWochenUebersicht(): array
{
    $wochenTage = ['Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag'];
    $wochenUebersicht = [];

    $currentDate = now();

    foreach ($wochenTage as $tag) {
        $datumObj = $currentDate->copy()->startOfWeek()->addDays(array_search($tag, $wochenTage));
        $datum = $datumObj->format('d.m.');
        $datumYmd = $datumObj->format('Y-m-d');

		$eroeffnungen = Eroeffnung::whereDate('vertragsbeginn', $datumYmd)
			->where('archiviert', 0)
			->select('id', 'vorname', 'nachname')
			->get()
			->map(fn($e) => [
				'id' => $e->id,
				'name' => $e->vorname . ' ' . $e->nachname,
			])
			->sortBy('name')
			->values();

		$mutationen = Mutation::whereDate('vertragsbeginn', $datumYmd)
			->where('archiviert', 0)
			->with('adUser:id,firstname,lastname')
			->get()
			->map(fn($m) => [
				'id' => $m->id,
				'name' => optional($m->adUser)->firstname . ' ' . optional($m->adUser)->lastname,
			])
			->sortBy('name')
			->values();

		$austritte = Austritt::whereDate('vertragsende', $datumYmd)
			->where('archiviert', 0)
			->with('adUser:id,firstname,lastname')
			->get()
			->map(fn($a) => [
				'id' => $a->id,
				'name' => optional($a->adUser)->firstname . ' ' . optional($a->adUser)->lastname,
			])
			->sortBy('name')
			->values();

        $wochenUebersicht[$tag] = [
            'datum' => $datum,
            'eroeffnungen' => $eroeffnungen,
            'mutationen' => $mutationen,
            'austritte' => $austritte,
        ];
    }

    return $wochenUebersicht;
}



    
    public function render()
    {
        return view("livewire.pages.dashboard")->layout("layouts.app", ["pageTitle" => "Dashboard",]);
    }
}