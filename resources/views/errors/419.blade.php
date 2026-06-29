@extends('errors.layout')

@section('title', 'Sesi Habis')
@section('code', '419')

@section('image/icon')
    <i class="bi bi-clock-history text-7xl text-pink-500 opacity-80"></i>
@endsection

@section('message')
    Sesi Anda telah berakhir karena terlalu lama tidak ada aktivitas. Silakan muat ulang halaman ini.
@endsection

@section('action')
    <button onclick="window.location.reload()" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-full text-white bg-pink-600 hover:bg-pink-700 shadow-md transition duration-150 ease-in-out">
        <i class="bi bi-arrow-clockwise mr-2"></i> Muat Ulang
    </button>
@endsection
