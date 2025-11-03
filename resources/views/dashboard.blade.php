@extends('dashboard.layouts.dashboard')

@section('content')
    @if (auth()->user()->role === 'masyarakat')
        @include('dashboard.partials.masyarakat')
    @else
        @include('dashboard.partials.admin')
    @endif
@endsection
