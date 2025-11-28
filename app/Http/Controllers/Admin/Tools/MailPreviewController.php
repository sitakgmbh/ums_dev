<?php

namespace App\Http\Controllers\Admin\Tools;

use App\Http\Controllers\Controller;
use App\Mail\Eroeffnungen\{
    Bestaetigung as EroeffnungBestaetigung,
    AuftragBerufskleider as EroeffnungAuftragBerufskleider,
    AuftragGarderobe as EroeffnungAuftragGarderobe,
    AuftragRaumbeschriftung as EroeffnungAuftragRaumbeschriftung,
    AuftragSap as EroeffnungAuftragSap,
	AuftragLei as EroeffnungAuftragLei,
    AuftragZutrittsrechte as EroeffnungAuftragZutrittsrechte,
    InfoMail as EroeffnungInfoMail
};
use App\Mail\Mutationen\{
    Bestaetigung as MutationBestaetigung,
    AuftragBerufskleider as MutationAuftragBerufskleider,
    AuftragGarderobe as MutationAuftragGarderobe,
    AuftragRaumbeschriftung as MutationAuftragRaumbeschriftung,
    AuftragSap as MutationAuftragSap,
	AuftragLei as MutationAuftragLei,
    AuftragZutrittsrechte as MutationAuftragZutrittsrechte,
    InfoMail as MutationInfoMail
};
use App\Mail\TestMail;
use App\Models\Eroeffnung;
use App\Models\Mutation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MailPreviewController extends Controller
{
    public function render(Request $request)
    {
        $mailable = $request->get('mailable');
        $modelId  = $request->get('model_id');

        if (!$mailable) abort(404, 'Kein Mailable angegeben');

        $mailable = Str::lower($mailable);

        $map = [
            // Eröffnungen
            'eroeffnungen.bestaetigung' => fn($id) => new EroeffnungBestaetigung(Eroeffnung::findOrFail($id)),
            'eroeffnungen.auftragberufskleider' => fn($id) => new EroeffnungAuftragBerufskleider(Eroeffnung::findOrFail($id)),
            'eroeffnungen.auftraggarderobe' => fn($id) => new EroeffnungAuftragGarderobe(Eroeffnung::findOrFail($id)),
            'eroeffnungen.auftragraumbeschriftung' => fn($id) => new EroeffnungAuftragRaumbeschriftung(Eroeffnung::findOrFail($id)),
            'eroeffnungen.auftragsap' => fn($id) => new EroeffnungAuftragSap(Eroeffnung::findOrFail($id)),
			'eroeffnungen.auftraglei' => fn($id) => new EroeffnungAuftragLei(Eroeffnung::findOrFail($id)),
            'eroeffnungen.auftragzutrittsrechte' => fn($id) => new EroeffnungAuftragZutrittsrechte(Eroeffnung::findOrFail($id)),
            'eroeffnungen.infomail' => fn($id) => new EroeffnungInfoMail(Eroeffnung::findOrFail($id)),

            // Mutationen
            'mutationen.bestaetigung' => fn($id) => new MutationBestaetigung(Mutation::findOrFail($id)),
            'mutationen.auftragberufskleider' => fn($id) => new MutationAuftragBerufskleider(Mutation::findOrFail($id)),
            'mutationen.auftraggarderobe' => fn($id) => new MutationAuftragGarderobe(Mutation::findOrFail($id)),
            'mutationen.auftragraumbeschriftung' => fn($id) => new MutationAuftragRaumbeschriftung(Mutation::findOrFail($id)),
            'mutationen.auftragsap' => fn($id) => new MutationAuftragSap(Mutation::findOrFail($id)),
			'mutationen.auftraglei' => fn($id) => new MutationAuftragLei(Mutation::findOrFail($id)),
            'mutationen.auftragzutrittsrechte' => fn($id) => new MutationAuftragZutrittsrechte(Mutation::findOrFail($id)),
            'mutationen.infomail' => fn($id) => new MutationInfoMail(Mutation::findOrFail($id)),
			
			// Sonstige
            'testmail' => fn() => new TestMail('demo@example.ch', url('/admin/tools/mail-preview/render?mailable=testmail')),
        ];

        if (!isset($map[$mailable])) abort(404, "Mailable '{$mailable}' nicht gefunden");

		$factory = $map[$mailable];
		$needsModel = !in_array($mailable, ['testmail']);
		$instance = $needsModel ? $factory($modelId) : $factory();

		$instance->build(); // Build damit Empfänger vorhanden ist

		$subject = $instance->subject ?? null;

		$viewName = $instance->view;
		
		if ($viewName instanceof \Illuminate\View\View) $viewName = $viewName->name();

		$data = method_exists($instance, 'buildViewData') ? $instance->buildViewData() : [];

		return view($viewName, array_merge($data, ['subject' => $subject]));
    }
}
