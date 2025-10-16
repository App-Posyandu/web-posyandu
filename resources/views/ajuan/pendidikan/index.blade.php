@extends('layouts.app')
@section('title', 'Ajuan Pendidikan')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Data Ajuan Pendidikan</h3>
                        <div class="card-tools">
                            <a href="{{ route('ajuan.pendidikan.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Tambah Ajuan
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif
                        <table class="table table-bordered table-striped" id="dataTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Usia</th>
                                    <th>Alamat</th>
                                    <th>Tanggal Ajuan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ajuanPendidikans as $ajuan)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $ajuan->nama }}</td>
                                        <td>{{ $ajuan->jenis_kelamin }}</td>
                                        <td>{{ $ajuan->usia }} tahun</td>
                                        <td>{{ $ajuan->alamat }}</td>
                                        <td>{{ $ajuan->created_at->format('d-m-Y') }}</td>
                                        <td>
                                            @if ($ajuan->status == 'pending')
                                                <span class="badge badge-warning">Pending</span>
                                            @elseif ($ajuan->status == 'approved')
                                                <span class="badge badge-success">Approved</span>
                                            @else
                                                <span class="badge badge-danger">Rejected</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('ajuan.pendidikan.show', $ajuan->id) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('ajuan.pendidikan.edit', $ajuan->id) }}" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('ajuan.pendidikan.destroy', $ajuan->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection