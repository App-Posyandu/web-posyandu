@extends('errors.layout')

@section('title', 'Sedang Pemeliharaan')
@section('code', '503')

@section('image/icon')
    <div class="inline-block animate-spin">
        <i class="bi bi-gear-fill text-7xl text-pink-500 opacity-80"></i>
    </div>
@endsection

@section('message')
    Sistem SAPA Posyandu saat ini sedang dalam pemeliharaan rutin untuk peningkatan layanan. Kami akan segera kembali!
@endsection

@section('action')
    <button onclick="window.location.reload()" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-full text-white bg-pink-600 hover:bg-pink-700 shadow-md transition duration-150 ease-in-out">
        <i class="bi bi-arrow-clockwise mr-2"></i> Coba Muat Ulang
    </button>
@endsection
