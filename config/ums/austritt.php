<?php

return [
    'taskDefinitions' => [
        [
            'field' => 'status_pep',
            'label' => 'PEP',
            'icon'  => 'mdi mdi-clock',
            'modal' => 'components.modals.austritte.pep',
        ],
        [
            'field' => 'status_kis',
            'label' => 'KIS',
            'icon'  => 'mdi mdi-doctor',
            'modal' => 'components.modals.austritte.kis',
        ],
        [
            'field' => 'status_streamline',
            'label' => 'Streamline',
            'icon'  => 'mdi mdi-badge-account-horizontal',
            'modal' => 'components.modals.austritte.streamline',
        ],
        [
            'field' => 'status_tel',
            'label' => 'Telefonie',
            'icon'  => 'mdi mdi-phone',
            'modal' => 'components.modals.austritte.telefonie',
        ],
        [
            'field' => 'status_alarmierung',
            'label' => 'Alarmierung',
            'icon'  => 'mdi mdi-bell',
            'modal' => 'components.modals.austritte.alarmierung',
        ],
        [
            'field' => 'status_logimen',
            'label' => 'LogiMen',
            'icon'  => 'mdi mdi-food-fork-drink',
            'modal' => 'components.modals.austritte.logimen',
        ],
    ],
    'detailsSections' => [
        'Austritt' => [
            'Vertragsende' => 'vertragsende',
            'Ticket'       => 'ticket_nr',
        ],
        'Personalien' => [
			'Anrede'   => 'adUser.anrede.name',
			'Titel'    => 'adUser.titel.name',
			'Vorname'  => 'adUser.firstname',
			'Nachname' => 'adUser.lastname',
        ],
		'Funktion' => [
			'Arbeitsort'         => 'adUser.arbeitsort.name',
			'Unternehmenseinheit'=> 'adUser.unternehmenseinheit.name',
			'Abteilung'          => 'adUser.abteilung.name',
			'Funktion'           => 'adUser.funktion.name',
		],
        'Active Directory' => [
            'Benutzername'   => 'adUser.username',
            'E-Mail-Adresse' => 'adUser.email',
        ],
        'Details' => [
            'Besitzer'     => 'owner.display_name',
			'Ticket'         => 'ticket_nr',
        ],
    ],
];
