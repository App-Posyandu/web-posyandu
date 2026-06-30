@extends('errors.layout')

@section('title', 'Akses Ditolak')
@section('code', '403')

@section('image/icon')
    <i class="bi bi-shield-lock-fill text-7xl text-pink-500 opacity-80"></i>
@endsection

@section('message')
    Maaf, Anda tidak memiliki hak akses untuk melihat halaman atau melakukan tindakan ini.
@endsection

@section('action')
    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-full text-white bg-pink-600 hover:bg-pink-700 shadow-md transition duration-150 ease-in-out">
        <i class="bi bi-house-door-fill mr-2"></i> Kembali ke Beranda
    </a>
@endsection
