<?php
namespace App\Livewire\Components\Modals\ActiveDirectory;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\AdUser;
use App\Models\SapExport;

class SapAdMapping extends BaseModal
{
    public $activeFilter = 'keine_personalnummer';
    public $data = [];
    
    protected function openWith(array $payload): bool
    {	
        $this->loadData();
        
        $this->title = "Details SAP ↔ AD";
        $this->size = "full-width";
        $this->position = "centered";
        $this->backdrop = false;
        $this->headerBg = "bg-primary";
        $this->headerText = "text-white";
        
        return true;
    }
    
    public function setFilter($filter)
    {
        $this->activeFilter = $filter;
        $this->loadData();
    }
    
	protected function loadData()
	{
		$this->data = collect(match($this->activeFilter) {
			'keine_personalnummer' => $this->getBenutzerOhnePersonalnummer(),
			'kein_ad_benutzer' => $this->getKeinAdBenutzer(),
			'kein_sap_eintrag' => $this->getKeinSapEintrag(),
			default => []
		});
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
            ->orderBy("display_name", "asc")
            ->get();
    }
    
    public function render()
    {
        return view("livewire.components.modals.active-directory.sap-ad-mapping");
    }
}