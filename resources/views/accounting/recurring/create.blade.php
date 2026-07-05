@extends('layouts.app')

@section('title', 'قالب قيد متكرر جديد')

@section('content')
@include('accounting.recurring._form', ['recurring' => null])
@endsection
