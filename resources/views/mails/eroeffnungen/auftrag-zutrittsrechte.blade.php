@extends('layouts.mail')

@section('header')
    <h2>Auftrag Zutrittsrechte</h2>
@endsection

@section('intro')
    <p>Hallo</p>
    <p>Bitte richte folgende Zutrittsrechte für einen neuen Mitarbeiter ein.</p>
	<ul style="margin:0; padding-left:0; list-style-position:inside;">
		@if($eroeffnung->key_wh_badge) <li>Badge Waldhaus</li> @endif
		@if($eroeffnung->key_wh_schluessel) <li>Schluessel Waldhaus</li> @endif
		@if($eroeffnung->key_be_badge) <li>Badge Beverin</li> @endif
		@if($eroeffnung->key_be_schluessel) <li>Schluessel Beverin</li> @endif
		@if($eroeffnung->key_rb_badge) <li>Badge Rothenbrunnen</li> @endif
		@if($eroeffnung->key_rb_schluessel) <li>Schluessel Rothenbrunnen</li> @endif
	</ul>
@endsection

@section('body')
    <p><strong>Wichtige Daten zum Antrag:</strong></p>
    @include('mails.partials.eroeffnung-details', ['eroeffnung' => $eroeffnung])
@endsection

@section('outro')
    @include('mails.partials.outro-standard')
@endsection
