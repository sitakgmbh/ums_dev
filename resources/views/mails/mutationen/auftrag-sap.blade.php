@extends('layouts.mail')

@section('header')
    <h2>Auftrag SAP-Mutation</h2>
@endsection

@section('intro')
    <p>Sehr geehrte Damen und Herren,</p>
    <p>Bitte bearbeiten Sie folgende Mutation:</p>

	<table>
		@if($hasSapRole)
			<tr>
				<td><strong>SAP-Rolle:</strong></td>
				<td>{{ $mutation->sapRolle->name }}</td>
			</tr>
		@endif

		@if($hasSapLei)
			<tr>
				<td><strong>Kommentar:</strong></td>
				<td>{{ $mutation->komm_lei }}</td>
			</tr>
		@endif

		@if($hasSapDelete)
			<tr>
				<td><strong>Aufgabe:</strong></td>
				<td>Bitte Benutzer löschen</td>
			</tr>
		@endif
	</table>

@endsection

@section('body')
    @include('mails.partials.mutation-details', ['mutation' => $mutation])
@endsection

@section('outro')
    <p>Vielen Dank und freundliche Grüsse,<br>PDGR ICT</p>
@endsection
