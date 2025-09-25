<?php

namespace App\Livewire\Pages\Admin\Tools;

use Livewire\Component;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class MailTest extends Component
{
    public string $to = 'patrik@sitak.ch';
    public ?string $status = null;
    public string $statusType = 'info';
    public ?string $preview = null;

    protected function rules(): array
    {
        return [
            'to' => ['required', 'email'],
        ];
    }

	public function send(): void
	{
		$this->validate();

		try {
			Mail::to($this->to)->send(new TestMail($this->to));

			$this->status = "Testmail erfolgreich an {$this->to} gesendet.";
			$this->statusType = 'success';

			// Vorschau raus
			$this->preview = null;

		} catch (\Throwable $e) {
			$this->status = "Fehler beim Senden: " . $e->getMessage();
			$this->statusType = 'danger';
		}
	}

public function render()
{
    // Mail-Config laden und Passwort entfernen
    $mailConfig = config('mail');
    if (isset($mailConfig['mailers']['smtp']['password'])) {
        $mailConfig['mailers']['smtp']['password'] = '********'; // maskieren
    }

    return view('livewire.pages.admin.tools.mail-test', [
        'mailConfig' => $mailConfig,
    ])->layoutData([
        'pageTitle' => 'Benutzerverwaltung',
    ]);
}


}
