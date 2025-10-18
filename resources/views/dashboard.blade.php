@extends('dashboard.layouts.dashboard')

@section('content')
    @if (Auth::user()->role === 'masyarakat')
        @include('dashboard.partials.masyarakat')
    @else
        @include('dashboard.partials.admin')
    @endif
@endsection
