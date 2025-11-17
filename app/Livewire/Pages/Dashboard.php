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
    // Anzeige-Reihenfolge
    $tage = ['Samstag', 'Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag'];

    $uebersicht = [];
    $heute = now()->format('Y-m-d');

    foreach ($tage as $tag) {

        // Zuordnung zu einem festen Wochenindex
        // (Montag = 0 ... Sonntag = 6)
        $index = match ($tag) {
            'Montag'     => 0,
            'Dienstag'   => 1,
            'Mittwoch'   => 2,
            'Donnerstag' => 3,
            'Freitag'    => 4,
            'Samstag'    => 5,
            'Sonntag'    => 6,
        };

        if ($tag === 'Samstag' || $tag === 'Sonntag') {
            // Samstag/Sonntag aus der VORWOCHE
            $basis = now()->copy()->subWeek()->startOfWeek(); // Montag vor einer Woche
        } else {
            // Rest aus der aktuellen Woche
            $basis = now()->copy()->startOfWeek(); // Dieser Montag
        }

        // Datum fuer den Tag berechnen
        $datumObj = $basis->copy()->addDays($index);
        $datumYmd = $datumObj->format('Y-m-d');

        // Daten laden
        $eroeffnungen = Eroeffnung::whereDate('vertragsbeginn', $datumYmd)
            ->where('archiviert', 0)
            ->select('id', 'vorname', 'nachname')
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'name' => $e->vorname.' '.$e->nachname,
            ])
            ->sortBy('name')
            ->values();

        $mutationen = Mutation::whereDate('vertragsbeginn', $datumYmd)
            ->where('archiviert', 0)
            ->with('adUser:id,firstname,lastname')
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'name' => optional($m->adUser)->firstname.' '.optional($m->adUser)->lastname,
            ])
            ->sortBy('name')
            ->values();

        $austritte = Austritt::whereDate('vertragsende', $datumYmd)
            ->where('archiviert', 0)
            ->with('adUser:id,firstname,lastname')
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'name' => optional($a->adUser)->firstname.' '.optional($a->adUser)->lastname,
            ])
            ->sortBy('name')
            ->values();

        // Zusammenbauen
        $uebersicht[$tag] = [
            'datum'        => $datumObj->format('d.m.'),
            'datum_ymd'    => $datumYmd,
            'heute'        => $datumYmd === $heute,
            'eroeffnungen' => $eroeffnungen,
            'mutationen'   => $mutationen,
            'austritte'    => $austritte,
        ];
    }

    return $uebersicht;
}




    
    public function render()
    {
        return view("livewire.pages.dashboard")->layout("layouts.app", ["pageTitle" => "Dashboard",]);
    }
}