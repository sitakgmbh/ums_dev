<?php

use App\Models\Eroeffnung;
use App\Models\Mutation;
use App\Models\Austritt;

return [

    /*
    |--------------------------------------------------------------------------
    | Ticketkonfigurationen für OTOBO
    |--------------------------------------------------------------------------
    |
    | Hier wird definiert, wie Tickets für die verschiedenen Typen aufgebaut
    | werden. Die Keys sind die Model-Klassen.
    |
    */

    Eroeffnung::class => [
        "title_prefix" => "Grundausstattung",
        "queue_id" => 21, // Personal
        "service_id" => 205, // Identity & Access Management
        "ticket_type_id" => 9, // Anfrage
        "state_id" => 4, // offen
        "priority_id" => 3, // normal
        "field_mapping" => [
            "anrede.name" => "Anrede",
            "vorname" => "Vorname",
            "nachname" => "Nachname",
            "antragsteller.display_name" => "Antragsteller",
            "bezugsperson.display_name" => "Bezugsperson",
            "vorlageBenutzer.display_name" => "Berechtigungen übernehmen von",
            "arbeitsort.name" => "Arbeitsort",
            "unternehmenseinheit.name" => "Unternehmenseinheit",
            "abteilung.name" => "Abteilung",
            "funktion.name" => "Funktion",
            "vertragsbeginn" => "Eintrittsdatum",
            "benutzername" => "Vorlaeufiger Benutzername",
            "email" => "Vorläufige E-Mail-Adresse",
        ],
    ],

    Mutation::class => [
		"title_prefix" => "Mutation",
		"queue_id" => 21, // Personal
		"service_id" => 205, // Identity & Access Management
		"ticket_type_id" => 9, // Anfrage
		"state_id" => 4, // offen
		"priority_id" => 3, // normal
		"field_mapping" => [
			"adUser.display_name" => "Benutzer",
			"antragsteller.display_name" => "Antragsteller",
			"vertragsbeginn" => "Vertragsbeginn",
		],
    ],

    Austritt::class => [
        "title_prefix" => "Austritt",
			"queue_id" => 21, // Personal
			"service_id" => 205, // Identity & Access Management
			"ticket_type_id" => 9, // Anfrage
			"state_id" => 4, // offen
			"priority_id" => 3, // normal
        "field_mapping" => [
			"adUser.display_name" => "Benutzer",
            "adUser.initials" => "Personalnummer",
            "adUser.username" => "Benutzername",
            "vertragsende" => "Austrittsdatum",
        ],
    ],

];
