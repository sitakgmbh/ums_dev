@extends('layouts.mail')

@section('header')
    <h2>Auftrag Garderobe</h2>
@endsection

@section('intro')
    <p>Sehr geehrte Damen und Herren,</p>
    <p>Bitte richte eine Garderobe für einen neuen Mitarbeiter ein:</p>
@endsection

@section('body')
    @include('mails.partials.eroeffnung-details', ['eroeffnung' => $eroeffnung])
@endsection

@section('outro')
    <p>Vielen Dank und freundliche Gruesse,<br>Ihre ICT</p>
@endsection
