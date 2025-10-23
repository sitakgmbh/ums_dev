@extends('layouts.mail')

@section('header')
    <h2>PC-Login Informationen</h2>
@endsection

@section('intro')
    <p>Hallo</p>
	<p>Nachfolgend findest du das PC-Login für <strong>{{ $eroeffnung->nachname }} {{ $eroeffnung->vorname }}</strong>. Dieses Login ist auch für NICE und PEP gültig.
	<p>Bitte behandle diese Informationen vertraulich.</p>
@endsection

@section('body')
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="width:200px; padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Vorname:</td>
            <td style="padding:5px; border-bottom:1px solid #ddd;">{{ $eroeffnung->vorname }}</td>
        </tr>
        <tr>
            <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Nachname:</td>
            <td style="padding:5px; border-bottom:1px solid #ddd;">{{ $eroeffnung->nachname }}</td>
        </tr>
        <tr>
            <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Benutzername:</td>
            <td style="padding:5px; border-bottom:1px solid #ddd;">{{ $eroeffnung->benutzername }}</td>
        </tr>
        <tr>
            <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">Passwort:</td>
            <td style="padding:5px; border-bottom:1px solid #ddd;">
                <code style="font-family:'Courier New', monospace; font-size:16px;">{{ $eroeffnung->passwort }}</code>
            </td>
        </tr>
        <tr>
            <td style="padding:5px; font-weight:bold; white-space:nowrap; border-bottom:1px solid #ddd;">E-Mail:</td>
            <td style="padding:5px; border-bottom:1px solid #ddd;">
                <a href="mailto:{{ $eroeffnung->email }}" style="color:#000000; text-decoration:none;">{{ $eroeffnung->email }}</a>
            </td>
        </tr>
    </table>
@endsection

@section('outro')
    @include('mails.partials.outro-standard')
@endsection
