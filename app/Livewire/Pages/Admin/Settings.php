<?php

namespace App\Livewire\Pages\Admin;

use Livewire\Component;
use App\Models\Setting;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Settings extends Component
{
    public bool $debug_mode = false;

    public function mount()
    {
        $this->debug_mode       = Setting::getValue('debug_mode', false);
    }

    public function save()
    {
        // Cache leeren für alle Keys
        foreach ([
            'debug_mode','mail_mailer','mail_host','mail_port',
            'mail_username','mail_password','mail_encryption',
            'mail_from_address','mail_from_name'
        ] as $key) {
            \Cache::forget("setting_{$key}");
        }

        Setting::updateOrCreate(['key' => 'debug_mode'], [
            'value' => $this->debug_mode, 'type' => 'bool'
        ]);

        session()->flash('success', 'Einstellungen gespeichert.');
    }

    public function render()
    {
        return view('livewire.pages.admin.settings')
            ->layoutData([
                'pageTitle' => 'Einstellungen',
            ]);
    }
}
