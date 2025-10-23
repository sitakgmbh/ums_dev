<?php

namespace App\Livewire\Pages\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout("layouts.app")]
class AdminDashboard extends Component
{
    public function render()
    {
        $groups = [
            "Allgemein" => [
                [
                    "title"       => "Einstellungen",
                    "description" => "Systemweite Einstellungen ändern",
                    "icon"        => "mdi mdi-cog-outline",
                    "color"       => "dark",
                    "route"       => "admin.settings",
                    "is_external" => false,
                ],
                [
                    "title"       => "Benutzerverwaltung",
                    "description" => "Benutzerzugriffe verwalten und Rollen zuweisen",
                    "icon"        => "mdi mdi-account-multiple-outline",
                    "color"       => "dark",
                    "route"       => "admin.users.index",
                    "is_external" => false,
                ],
            ],
            "Werkzeuge" => [
                [
                    "title"       => "Aufgabenplaner",
                    "description" => "Tasks einsehen und ausführen",
                    "icon"        => "mdi mdi-console",
                    "color"       => "dark",
                    "route"       => "admin.tools.task-scheduler",
                    "is_external" => false,
                ],
                [
                    "title"       => "Mail-Tool",
                    "description" => "Mailables rendern und senden",
                    "icon"        => "mdi mdi-email-outline",
                    "color"       => "dark",
                    "route"       => "admin.tools.mail-tool",
                    "is_external" => false,
                ],
                [
                    "title"       => "API",
                    "description" => "Interaktive API mit Swagger",
                    "icon"        => "mdi mdi-connection",
                    "color"       => "dark",
                    "route"       => "/api/documentation", 
                    "is_external" => true,
                ],
            ],
            "Info" => [
                [
                    "title"       => "Logs",
                    "description" => "Systemlogs und Logfiles einsehen",
                    "icon"        => "mdi mdi-clipboard-text-outline",
                    "color"       => "dark",
                    "route"       => "admin.logs.index",
                    "is_external" => false,
                ],
                [
                    "title"       => "Server",
                    "description" => "Informationen zum System",
                    "icon"        => "mdi mdi-server-outline",
                    "color"       => "dark",
                    "route"       => "admin.server-info",
                    "is_external" => false,
                ],
                [
                    "title"       => "Changelog",
                    "description" => "Änderungen von UMS einsehen",
                    "icon"        => "mdi mdi-newspaper-variant-outline",
                    "color"       => "dark",
                    "route"       => "admin.changelog",
                    "is_external" => false,
                ],
            ],
        ];

        return view("livewire.pages.admin.dashboard", compact("groups"))
            ->layoutData([
                "pageTitle" => "Systemsteuerung",
            ]);
    }
}
