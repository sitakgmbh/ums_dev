<?php

return [

    /*
    |--------------------------------------------------------------------------
    | M365 Lizenz → AD-Gruppen Mapping
    |--------------------------------------------------------------------------
    |
    | Dieses Array ordnet Lizenzen den entsprechenden Active Directory Gruppen zu.
    | Zusätzlich kann eine Standardgruppe ("default") definiert werden, die
    | verwendet wird, falls keine passende Lizenz gefunden wird.
    |
    */

    "licenses" => [
        "EOP1" => "m365_EOP1",
        "EOP2" => "m365_EOP2",
        "E3"   => "m365_E3",
    ],

    /*
    |--------------------------------------------------------------------------
    | Standard-Lizenzgruppe
    |--------------------------------------------------------------------------
    |
    | Diese Lizenz wird standardmässig verwendet.
    |
    */

    "default" => "m365_E3",
];
