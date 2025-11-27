<?php

namespace App\Livewire\Components\Modals\ActiveDirectory;

use App\Livewire\Components\Modals\BaseModal;
use App\Services\Sap\SapAdStatusService;

class SapAdMapping extends BaseModal
{
    public $activeFilter = 'keine_personalnummer';
    public $data = [];
    public $excludedInitials = [];
	public $excludedUsernames = [];
	public $secondaryPns = [];
    public $filters = ['keine_personalnummer', 'kein_sap_eintrag', 'kein_ad_benutzer'];

    protected function openWith(array $payload): bool
    {   
        $this->loadData(app(SapAdStatusService::class));
        
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
        $this->loadData(app(SapAdStatusService::class));
    }
    
	protected function loadData(SapAdStatusService $SapAdStatusService)
	{
		$this->data = $SapAdStatusService->getFilteredData($this->activeFilter);
		$this->excludedInitials = $SapAdStatusService->getExcludedInitials();
		$this->excludedUsernames = $SapAdStatusService->getExcludedUsernames();
		$this->secondaryPns = $SapAdStatusService->getSecondaryPersonalnummern();
	}

    public function render()
    {
        return view("livewire.components.modals.active-directory.sap-ad-mapping");
    }
}