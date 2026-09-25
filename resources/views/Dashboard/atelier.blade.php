@extends('layouts.atelier')

@section('title', 'لوحة التحكم — Magzani')

@section('page-title', $pageTitle)

@section('content')
    <livewire:dashboard.dashboard :range="$initialRange" />
@endsection
