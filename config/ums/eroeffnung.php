<?php

return [
	"taskDefinitions" => [
		[
			"field" => "status_ad",
			"label" => "AD-Benutzer",
			"icon" => "mdi mdi-account",
			"modal" => "components.modals.eroeffnungen.ad",
		],
		[
            "field" => "status_pep",
            "label" => "PEP-Benutzer",
            "icon" => "mdi mdi-clock",
            "modal" => "components.modals.eroeffnungen.pep",
        ],
		[
			"field" => "status_kis",
			"label" => "KIS-Benutzer",
			"icon" => "mdi mdi-doctor",
			"modal" => "components.modals.eroeffnungen.kis",
		],
		[
			"field" => "status_tel",
			"label" => "Telefonie",
			"icon" => "mdi mdi-phone",
			"modal" => "components.modals.eroeffnungen.telefonie",
		],
		[
			"field" => "status_auftrag",
			"label" => "Aufträge",
			"icon" => "mdi mdi-clipboard-text",
			"modal" => "components.modals.eroeffnungen.auftraege",
		],
		[
			"field" => "status_info",
			"label" => "Info-Mail",
			"icon" => "mdi mdi-information-variant",
			"modal" => "components.modals.eroeffnungen.info-mail",
		],
	],
	"telefonie" => [
		"cloudExt1" => [
			"CP100_Intern",
			"CP100_Standard",
			"CP100_UC_Standard_APP",
			"CP100E_intern",
			"CP205_Standard",
			"CP600E_Standard",
			"CP600E_Standard_APP",
			"CP600E_UC_Standard_APP",
			"CP700_Reception",
			"CP700_Standard_APP",
			"MOB_International",
			"MOB_Standard",
			"UC_Standard",
			"UC_Standard_APP",
		],
		"cloudExt2" => [
			"NA_INF",
			"NA_KRM",
			"NA_MNO",
			"NA_SEC",
			"NA_WLS",
		],
		"cloudExt3" => [
			"NA_OSA",
		],
	],
	"mail" => [
		"sap" => [
			"to" => ["patrik@sitak.ch"],
			"cc" => ["patrik@senti.bz"],
		],
		"sap_lei" => [
			"to" => ["patrik@sitak.ch"],
			"cc" => ["patrik@senti.bz"],
		],
		"raumbeschriftung_wh" => [
			"to" => ["patrik@sitak.ch"],
			"cc" => [],
		],
		"raumbeschriftung_be" => [
			"to" => ["patrik@sitak.ch"],
			"cc" => [],
		],
		"raumbeschriftung_rb" => [
			"to" => ["patrik@sitak.ch"],
			"cc" => [],
		],
		"berufskleider" => [
			"to" => ["patrik@sitak.ch"],
			"cc" => ["patrik@senti.bz"],
		],
		"garderobe" => [
			"to" => ["patrik@sitak.ch"],
			"cc" => ["patrik@senti.bz"],
		],
		"zutrittsrechte_wh" => [
			"to" => ["patrik@sitak.ch"],
			"cc" => ["patrik@senti.bz"],
		],
		"zutrittsrechte_be" => [
			"to" => ["patrik@sitak.ch"],
			"cc" => ["patrik@senti.bz"],
		],
		"zutrittsrechte_rb" => [
			"to" => ["patrik@sitak.ch"],
			"cc" => ["patrik@senti.bz"],
		],
		"info" => [
			"to" => [],
			"cc" => [],
		],
		"info-hr" => [
			"to" => ["patrik@sitak.ch", "patrik@senti.bz"],
		],
	],
    "detailsSections" => [
        "Personalien" => [
            "Anrede" => "anrede.name",
            "Titel" => "titel.name",
            "Vorname" => "vorname",
            "Nachname" => "nachname",
        ],
        "Funktion" => [
            "Arbeitsort" => "arbeitsort.name",
            "Unternehmenseinheit" => "unternehmenseinheit.name",
            "Abteilung" => "abteilung.name",
			"Zweite Abteilung" => "abteilung2.name",
            "Funktion" => "funktion.name",
        ],
        "Details" => [
            "Eintrittsdatum" => "vertragsbeginn",
            "Wiedereintritt" => "wiedereintritt",
			"Vor Eintritt lizenzieren" => "vorab_lizenzierung",
            "Antragsteller" => "antragsteller.display_name",
            "Bezugsperson" => "bezugsperson.display_name",
			"Berechtigungen" => "vorlageBenutzer.display_name",
			"Besitzer" => "owner.display_name",
            "Ticket" => "ticket_nr",
        ],
        "Active Directory" => [
            "Benutzername" => "benutzername",
			"Passwort" => "passwort",
            "E-Mail-Adresse" => "email",
        ],
    ],
];
