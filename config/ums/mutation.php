<?php

return [
	'taskDefinitions' => [
		[
			'field'   => 'status_ad',
			'label'   => 'Berechtigungen',
			'icon'    => 'mdi mdi-account-cog',
			'modal'   => 'components.modals.mutationen.ad',
		],
		[
			'field'   => 'status_pep',
			'label'   => 'PEP-Benutzer',
			'icon'    => 'mdi mdi-clock',
			'modal'   => 'components.modals.mutationen.pep',
		],
		[
			'field'   => 'status_kis',
			'label'   => 'KIS-Benutzer',
			'icon'    => 'mdi mdi-doctor',
			'modal'   => 'components.modals.mutationen.kis',
		],
		[
			'field'   => 'status_mail',
			'label'   => 'E-Mail-Adresse',
			'icon'    => 'mdi mdi-email',
			'modal'   => 'components.modals.mutationen.email-bearbeiten',
		],
		[
			'field'   => 'status_tel',
			'label'   => 'Telefonie',
			'icon'    => 'mdi mdi-phone',
			'modal'   => 'components.modals.mutationen.telefonie',
		],
		[
			'field'   => 'status_auftrag',
			'label'   => 'Aufträge',
			'icon'    => 'mdi mdi-clipboard-text',
			'modal'   => 'components.modals.mutationen.auftraege',
		],
		[
			'field'   => 'status_info',
			'label'   => 'Info-Mail',
			'icon'    => 'mdi mdi-information-variant',
			'modal'   => 'components.modals.mutationen.info-mail',
		],
	],
	'telefonie' => [
		'cloudExt1' => [
			'CP100_Intern',
			'CP100_Standard',
			'CP100_UC_Standard_APP',
			'CP100E_intern',
			'CP205_Standard',
			'CP600E_Standard',
			'CP600E_Standard_APP',
			'CP600E_UC_Standard_APP',
			'CP700_Reception',
			'CP700_Standard_APP',
			'MOB_International',
			'MOB_Standard',
			'UC_Standard',
			'UC_Standard_APP',
		],
		'cloudExt2' => [
			'NA_INF',
			'NA_KRM',
			'NA_MNO',
			'NA_SEC',
			'NA_WLS',
		],
		'cloudExt3' => [
			'NA_OSA',
		],
	],
	'mail' => [
		'sap' => [
			'to' => ['patrik@sitak.ch'],
			'cc' => ['patrik@senti.bz'],
		],
		'sap_lei' => [
			'to' => ['patrik@sitak.ch'],
			"cc" => ["patrik@senti.bz"],
		],
		'raumbeschriftung_wh' => [
			'to' => ['patrik@sitak.ch'],
			'cc' => [],
		],
		'raumbeschriftung_be' => [
			'to' => ['patrik@sitak.ch'],
			'cc' => [],
		],
		'raumbeschriftung_rb' => [
			'to' => ['patrik@sitak.ch'],
			'cc' => [],
		],
		'berufskleider' => [
			'to' => ['patrik@sitak.ch'],
			'cc' => ['patrik@senti.bz'],
		],
		'garderobe' => [
			'to' => ['patrik@sitak.ch'],
			'cc' => ['patrik@senti.bz'],
		],
		'zutrittsrechte_wh' => [
			'to' => ['patrik@sitak.ch'],
			'cc' => ['patrik@senti.bz'],
		],
		'zutrittsrechte_be' => [
			'to' => ['patrik@sitak.ch'],
			'cc' => ['patrik@senti.bz'],
		],
		'zutrittsrechte_rb' => [
			'to' => ['patrik@sitak.ch'],
			'cc' => ['patrik@senti.bz'],
		],
		'info' => [
			'to' => [],
			'cc' => [],
		],
	],
    'detailsSections' => [
        'Personalien neu' => [
			'Anrede'   => 'anrede.name',
			'Titel'    => 'titel.name',
			'Vorname'  => 'vorname',
			'Nachname' => 'nachname',
			'Arbeitsort'         => 'arbeitsort.name',
			'Unternehmenseinheit'=> 'unternehmenseinheit.name',
			'Abteilung'          => 'abteilung.name',
			'Funktion'           => 'funktion.name',
        ],
        'Personalien alt' => [
			'Anrede'   => 'anredeOld.name',
			'Titel'    => 'titelOld.name',
			'Vorname'  => 'vorname_old',
			'Nachname' => 'nachname_old',
			'Arbeitsort'         => 'arbeitsortOld.name',
			'Unternehmenseinheit'=> 'unternehmenseinheitOld.name',
			'Abteilung'          => 'abteilungOld.name',
			'Funktion'           => 'funktionOld.name',
        ],
        'Zweite Abteilung' => [
            'Name'          => 'abteilung2.name',
        ],
        'Details' => [
            'Änderungsdatum' => 'vertragsbeginn',
            'Antragsteller'  => 'antragsteller.display_name',
			'Berechtigungen'   => 'vorlageBenutzer.display_name',
            'Besitzer'     => 'owner.display_name',
            'Ticket'         => 'ticket_nr',
        ],
        'Active Directory' => [
            'Benutzername'   => 'adUser.username',
            'E-Mail-Adresse' => 'adUser.email',
        ],
    ],
];
