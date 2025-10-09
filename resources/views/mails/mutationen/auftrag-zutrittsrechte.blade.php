@extends('layouts.mail')

@section('header')
    <h2>Auftrag Zutrittsrechte</h2>
@endsection

@section('intro')
    <p>Sehr geehrte Damen und Herren,</p>
    <p>bitte vergeben Sie Zutrittsrechte für folgenden neuen Mitarbeiter:</p>
@endsection

@section('body')
    {{-- Basis-Infos zur Eröffnung --}}
    @include('mails.partials.eroeffnung-details', ['eroeffnung' => $eroeffnung])

    <p><strong>Angeforderte Rechte:</strong></p>
    <ul>
        @if($eroeffnung->key_wh_badge) <li>Badge Waldhaus</li> @endif
        @if($eroeffnung->key_wh_schluessel) <li>Schluessel Waldhaus</li> @endif
        @if($eroeffnung->key_be_badge) <li>Badge Beverin</li> @endif
        @if($eroeffnung->key_be_schluessel) <li>Schluessel Beverin</li> @endif
        @if($eroeffnung->key_rb_badge) <li>Badge Rothenbrunnen</li> @endif
        @if($eroeffnung->key_rb_schluessel) <li>Schluessel Rothenbrunnen</li> @endif
    </ul>
@endsection

@section('outro')
    <p>Vielen Dank und freundliche Gruesse,<br>Ihre ICT</p>
@endsection
