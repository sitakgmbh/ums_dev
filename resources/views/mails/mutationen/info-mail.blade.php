@extends('layouts.mail')

@section('header')
    <h2>PC-Login Informationen</h2>
@endsection

@section('intro')
    <p>Sehr geehrte Damen und Herren,</p>
    <p>nachfolgend finden Sie die PC-Login-Daten für den neuen Mitarbeiter:</p>
@endsection

@section('body')
	<table style="width:100%; border-collapse:collapse;">
		<tr>
			<td style="width:200px; padding:2px 6px; font-weight:bold;">Vorname</td>
			<td>{{ $eroeffnung->vorname }}</td>
		</tr>
		<tr>
			<td style="padding:2px 6px; font-weight:bold;">Nachname</td>
			<td>{{ $eroeffnung->nachname }}</td>
		</tr>
		<tr>
			<td style="padding:2px 6px; font-weight:bold;">Benutzername</td>
			<td>{{ $eroeffnung->benutzername }}</td>
		</tr>
		<tr>
			<td style="padding:2px 6px; font-weight:bold;">Passwort</td>
			<td><code style="font-family:'Courier New', monospace; font-size:16px;">{{ $eroeffnung->passwort }}</code></td>
		</tr>
		<tr>
			<td style="padding:2px 6px; font-weight:bold;">E-Mail</td>
			<td>{{ $eroeffnung->email }}</td>
		</tr>
	</table>

@endsection

@section('outro')
    <p>Bitte geben Sie diese Informationen vertraulich an den Mitarbeiter weiter.</p>
    <p>Vielen Dank und freundliche Gruesse,<br>Ihre ICT</p>
@endsection
