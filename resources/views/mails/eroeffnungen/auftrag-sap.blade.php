@extends('layouts.mail')

@section('header')
    <h2>Auftrag SAP-Eröffnung</h2>
@endsection

@section('intro')
    <p>Sehr geehrte Damen und Herren</p>
	<p>Bitte erstellen Sie einen neuen SAP-Benutzer für <strong>{{ $eroeffnung->vorname }} {{ $eroeffnung->nachname }}</strong>.</p>
	<p>Rolle: {{ $eroeffnung->sapRolle?->name ?? '-' }}</p>
	
	@if($eroeffnung->is_lei)
		<p>Es handelt sich um einen Leistungserbringer.</p>
	@endif

@endsection

@section('body')
	<p><strong>Wichtige Daten zum Antrag:</strong></p>
    @include('mails.partials.eroeffnung-details', ['eroeffnung' => $eroeffnung])
@endsection

@section('outro')
    @include('mails.partials.outro-standard')
@endsection