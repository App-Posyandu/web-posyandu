@extends('layouts.app')

@section('content')
<div class="p-6 bg-white shadow rounded-md">

    <h2 class="text-xl font-bold mb-4">Import Pengguna</h2>

    @if(session('success'))
        <div class="p-3 bg-green-100 text-green-800 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.users.import.process') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <label class="block font-semibold mb-1">Upload File Excel:</label>
        <input type="file" name="file" class="border p-2 rounded w-full">

        <button class="mt-4 px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700">
            Import
        </button>

        <a href="{{ route('admin.users.index') }}"
           class="px-4 py-2 ml-2 bg-gray-300 rounded text-gray-800 hover:bg-gray-400">
            Kembali
        </a>
    </form>

</div>
@endsection
