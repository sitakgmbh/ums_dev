@extends('layouts.mail')

@section('header')
    <h2>Auftrag SAP-Mutation</h2>
@endsection

@section('intro')
    <p>Sehr geehrte Damen und Herren</p>

    @if($mutation->sap_delete)
        <p>Bitte löschen Sie den SAP-Benutzer von <strong>{{ $mutation->adUser->display_name }}</strong>.</p>
    @else
        <p>Bitte bearbeiten Sie folgende Mutation:</p>

        <p>Rolle: {{ $mutation->sapRolle?->name ?? '-' }}</p>
        <p>Leistungserbringer: {{ $mutation->is_lei ? 'Ja' : 'Nein' }}</p>

        @if($mutation->komm_lei)
		<div style="height:10px;"></div>
		<p><strong>Kommentar:</strong></p>
            <p><em>{{ $mutation->komm_lei }}</em></p>
        @endif
    @endif
@endsection

@section('body')
    <p><strong>Wichtige Daten zum Antrag:</strong></p>
    @include('mails.partials.mutation-details', ['mutation' => $mutation])
@endsection

@section('outro')
    @include('mails.partials.outro-standard')
@endsection
