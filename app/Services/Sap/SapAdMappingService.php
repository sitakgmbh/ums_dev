<?php

namespace App\Services\Sap;

use App\Models\AdUser;
use App\Models\SapExport;
use App\Models\Setting;

class SapAdMappingService
{
    protected $excludedInitials;

    public function __construct()
    {
        $this->excludedInitials = $this->getExcludedInitials();
    }

    public function getFilteredData(string $filter)
    {
        return match($filter) {
            'keine_personalnummer' => $this->getBenutzerOhnePersonalnummer(),
            'kein_ad_benutzer' => $this->getKeinAdBenutzer(),
            'kein_sap_eintrag' => $this->getKeinSapEintrag(),
            default => collect([]),
        };
    }

    public function getExcludedInitials(): array
    {
        return explode(',', Setting::getValue('personalnummer_abgleich_excludes', ''));
    }

    protected function getBenutzerOhnePersonalnummer()
    {
        return AdUser::where(function($query) {
                $query->whereNull("initials")
                      ->orWhere("initials", "99999");
            })
            ->where("is_existing", true)
            ->orderBy("display_name", "asc")
            ->get();
    }

    protected function getKeinAdBenutzer()
    {
        return SapExport::whereNull('ad_user_id')
            ->where(function($query) {
                $query->whereNull('d_einda')
                      ->orWhereRaw("STR_TO_DATE(d_einda, '%Y%m%d') <= CURDATE()");
            })
            ->orderBy('d_name', 'asc')
            ->get();
    }

    protected function getKeinSapEintrag()
    {
        return AdUser::whereDoesntHave('sapExport')
            ->where("is_existing", true)
            ->where("is_enabled", true)
            ->whereNotNull('initials')
            ->where('initials', '!=', '99999')
            ->where('initials', '!=', '11111')
            ->where('initials', '!=', '00000')
            ->orderBy("display_name", "asc")
            ->get();
    }
}