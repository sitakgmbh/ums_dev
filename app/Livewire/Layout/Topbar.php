<?php

namespace App\Livewire\Layout;

use Illuminate\Support\Facades\Auth;
use App\Models\Incident;
use Livewire\Component;

class Topbar extends Component
{
    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route("login");
    }

    public function toggle()
    {
        $user = auth()->user();
        $current = (bool) $user->getSetting("darkmode_enabled", false);

        $new = !$current;
        $user->setSetting("darkmode_enabled", $new);

        session()->put("darkmode_enabled", $new);
        session()->save();

        $this->dispatch("theme-changed", dark: $new);
    }

    public function render()
    {
        $openIncidents = Incident::open()
            ->with(['creator'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.layout.topbar', [
            'openIncidents' => $openIncidents,
        ]);
    }
}
