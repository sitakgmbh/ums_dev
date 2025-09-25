<?php

namespace App\Livewire\Pages\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class AdminDashboard extends Component
{
    public function render()
    {
        $groups = [
            'Allgemein' => [
                [
                    'title'       => 'Einstellungen',
                    'description' => 'Systemweite Einstellungen ändern',
                    'icon'        => 'mdi mdi-cog-outline',
                    'color'       => 'dark',
                    'route'       => 'admin.settings',
                    'is_external' => false,
                ],
                [
                    'title'       => 'Benutzerverwaltung',
                    'description' => 'Benutzerzugriffe verwalten und Rollen zuweisen',
                    'icon'        => 'mdi mdi-account-multiple-outline',
                    'color'       => 'dark',
                    'route'       => 'admin.users.index',
                    'is_external' => false,
                ],
                [
                    'title'       => 'Logs',
                    'description' => 'Systemlogs und Logfiles einsehen',
                    'icon'        => 'mdi mdi-clipboard-text-outline',
                    'color'       => 'dark',
                    'route'       => 'admin.logs.index',
                    'is_external' => false,
                ],
            ],
            'Wartung und Werkzeuge' => [
                [
                    'title'       => 'Artisan',
                    'description' => 'Befehle ausführen',
                    'icon'        => 'mdi mdi-console',
                    'color'       => 'dark',
                    'route'       => 'admin.tools.artisan',
                    'is_external' => false,
                ],
                [
                    'title'       => 'Testmail senden',
                    'description' => 'Konfiguration prüfen',
                    'icon'        => 'mdi mdi-email-fast-outline',
                    'color'       => 'dark',
                    'route'       => 'admin.tools.mail-test',
                    'is_external' => false,
                ],
                [
                    'title'       => 'API',
                    'description' => 'Interaktive API mit Swagger',
                    'icon'        => 'mdi mdi-connection',
                    'color'       => 'dark',
                    'route'       => '/api/documentation', 
                    'is_external' => true,
                ],
            ],
        ];

        return view('livewire.pages.admin.dashboard', compact('groups'))
            ->layoutData([
                'pageTitle' => 'Systemsteuerung',
            ]);
    }
}
