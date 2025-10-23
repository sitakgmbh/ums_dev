@extends('layouts.mail')

@section('header')
    <h2>Auftrag Garderobe</h2>
@endsection

@section('intro')
    <p>Hallo</p>
    <p>Bitte bearbeite folgende Anfrage bzgl. einer Garderobe:</p>
	<p>{{ $mutation->komm_garderobe }}</p>
@endsection

@section('body')
    @include('mails.partials.mutation-details', ['mutation' => $mutation])
@endsection

@section('outro')
    @include('mails.partials.outro-standard')
@endsection
