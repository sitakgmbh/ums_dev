<?php

namespace App\Livewire\Pages\Admin\Tools;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
	public function render()
	{
		return view('livewire.pages.admin.tools.index')
			->layoutData([
				'pageTitle' => 'Tools Übersicht',
			]);
	}
}
