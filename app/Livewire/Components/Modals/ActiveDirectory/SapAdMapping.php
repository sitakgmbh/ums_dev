<?php

namespace App\Livewire\Components\Modals\ActiveDirectory;

use App\Livewire\Components\Modals\BaseModal;
use App\Services\Sap\SapAdMappingService;

class SapAdMapping extends BaseModal
{
    public $activeFilter = 'keine_personalnummer';
    public $data = [];
    public $excludedInitials = [];
    public $filters = ['keine_personalnummer', 'kein_ad_benutzer', 'kein_sap_eintrag'];

    protected function openWith(array $payload): bool
    {   
        $this->loadData(app(SapAdMappingService::class));
        
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
        $this->loadData(app(SapAdMappingService::class));
    }
    
    protected function loadData(SapAdMappingService $sapAdMappingService)
    {
        $this->data = $sapAdMappingService->getFilteredData($this->activeFilter);
        $this->excludedInitials = $sapAdMappingService->getExcludedInitials();
    }
    
    public function render()
    {
        return view("livewire.components.modals.active-directory.sap-ad-mapping");
    }
}