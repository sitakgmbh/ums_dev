@extends('layouts.mail')

@section('header')
    <h2>Auftrag SAP-Mutation</h2>
@endsection

@section('intro')
    <p>Sehr geehrte Damen und Herren</p>

	@if($mutation->sap_rolle_id)
		<p>Bitte erstellen Sie einen neuen SAP-Benutzer für <strong>{{ $mutation->adUser->display_name }}</strong> mit der Rolle <strong>{{ $mutation->sapRolle?->name ?? '-' }}</strong>.</p>
	@endif

	@if($mutation->komm_lei)
		<p>Es handelt sich um einen Leistungserbringer. Kommentar:</p>
		<p>{{ $mutation->komm_lei }}</p>
	@endif

	@if($mutation->sap_delete)
		<p>Bitte löschen Sie den SAP-Benutzer von <strong>{{ $mutation->adUser->display_name }}</strong>.</p>
	@endif

@endsection

@section('body')
	<p><strong>Wichtige Daten zum Antrag:</strong></p>
    @include('mails.partials.mutation-details', ['mutation' => $mutation])
@endsection

@section('outro')
    @include('mails.partials.outro-standard')
@endsection
