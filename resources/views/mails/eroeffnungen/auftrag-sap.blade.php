@extends('layouts.mail')

@section('header')
    <h2>Auftrag SAP-Eröffnung</h2>
@endsection

@section('intro')
    <p>Sehr geehrte Damen und Herren,</p>
    <p>Bitte führen Sie die folgenden SAP-Eröffnungen durch:</p>

    <table>
        @if($hasSapUser)
            <tr>
                <td><strong>SAP-Benutzer:</strong></td>
                <td>Bitte neuen SAP-Benutzer für {{ $eroeffnung->vorname }} {{ $eroeffnung->nachname }} erstellen.</td>
            </tr>
            <tr>
                <td><strong>Rolle:</strong></td>
                <td>{{ $eroeffnung->sapRolle?->name ?? '-' }}</td>
            </tr>
        @endif

        @if($hasSapLei)
            <tr>
                <td><strong>SAP-Leistungserbringer:</strong></td>
                <td>Es wurde ein SAP-Leistungserbringer für {{ $eroeffnung->vorname }} {{ $eroeffnung->nachname }} bestellt.</td>
            </tr>
        @endif
    </table>
@endsection

@section('body')
    @include('mails.partials.eroeffnung-details', ['eroeffnung' => $eroeffnung])
@endsection

@section('outro')
    <p>Vielen Dank und freundliche Grüsse,<br>PDGR ICT</p>
@endsection
