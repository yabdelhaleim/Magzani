@extends('layouts.app')

@section('title', 'تعديل قالب قيد متكرر')

@section('content')
@include('accounting.recurring._form', ['recurring' => $recurring])
@endsection
