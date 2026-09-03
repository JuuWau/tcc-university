@extends('errors::minimal')

@section('title', __('Acesso negado'))

@section('code', '403')

@section('message', __('Você não possui permissão para acessar esta página.'))

@section('styles')
    <style>
        body {
            background-color: white !important;
        }
    </style>
@endsection