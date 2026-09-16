@extends('dashboard.layouts.dashboard')
@section('title', 'Data Tabel ' . $table)
@section('content')
<div class="w-full mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-4 sm:p-6 lg:p-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div class="w-full md:w-1/2 flex flex-col md:flex-row items-start md:items-center gap-4">
                <h2 class="text-2xl font-bold text-gray-800 whitespace-nowrap">
                    <i class="bi bi-table mr-2 text-pink-500"></i>
                    Data Tabel: <span class="font-mono bg-gray-50 px-2 py-1 rounded border">{{ $table }}</span>
                </h2>
            </div>
            <div class="w-full md:w-1/2 flex flex-col sm:flex-row justify-end gap-2">
                @if(!$isProtected)
                <button onclick="openCreateModal()" class="whitespace-nowrap px-4 py-2 bg-green-500 text-white rounded-md text-sm font-semibold hover:bg-green-600 transition inline-flex items-center justify-center">
                    <i class="bi bi-plus-circle mr-2"></i> Tambah Data
                </button>
                @endif
                <a href="{{ route('admin.database.index') }}" class="whitespace-nowrap px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm font-semibold hover:bg-gray-300 transition text-center inline-flex items-center justify-center">
                    <i class="bi bi-arrow-left mr-2"></i> Kembali ke Daftar
                </a>
            </div>
        </div>

        <div class="mb-4 bg-gray-50 p-4 rounded-lg border flex flex-col md:flex-row md:items-center justify-between gap-4">
            <form id="searchTableForm" action="{{ route('admin.database.show', $table) }}" method="GET" class="w-full flex flex-col sm:flex-row gap-2 items-center">
                <select id="searchColumnSelect" name="search_column" class="w-full sm:w-auto px-4 py-2 border rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500 text-sm">
                    <option value="">-- Pilih Kolom --</option>
                    @foreach ($columns as $column)
                        <option value="{{ $column }}" {{ (isset($searchColumn) && $searchColumn == $column) ? 'selected' : '' }}>
                            {{ $column }}
                        </option>
                    @endforeach
                </select>
                <input id="searchInput" type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari data..." class="w-full sm:w-1/2 px-4 py-2 border rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500 text-sm">
                <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-pink-500 text-white rounded-md text-sm font-semibold hover:bg-pink-600 transition">
                    <i class="bi bi-search mr-1"></i> Cari
                </button>
                @if(isset($search) && $search !== '')
                    <a href="{{ route('admin.database.show', $table) }}" class="w-full sm:w-auto px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm font-semibold hover:bg-gray-300 transition text-center">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        @if($isProtected)
        <div class="mb-4 bg-yellow-50 border border-yellow-300 text-yellow-800 px-4 py-3 rounded-lg text-sm">
            <i class="bi bi-shield-lock mr-2"></i>
            <strong>Tabel Dilindungi:</strong> Tabel ini dikelola oleh sistem dan tidak dapat ditambah, diedit, atau dihapus secara manual.
        </div>
        @endif

        <div class="text-gray-900">
            <div class="w-full max-w-full overflow-x-auto border rounded-lg">
                <table class="min-w-full whitespace-nowrap divide-y divide-gray-200 table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            @if(!$isProtected)
                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap sticky left-0 bg-gray-50 z-10">
                                Aksi
                            </th>
                            @endif
                            @foreach ($columns as $column)
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                    {{ $column }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($records as $record)
                            <tr class="hover:bg-gray-50 transition-colors">
                                @if(!$isProtected)
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-center sticky left-0 bg-white z-10 border-r">
                                    <div class="flex items-center justify-center gap-1">
                                        <button onclick="openEditModal('{{ $record->{$primaryKey} ?? '' }}')" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-800 transition" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button onclick="confirmDelete('{{ $record->{$primaryKey} ?? '' }}')" class="inline-flex items-center justify-center w-8 h-8 rounded-md bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-800 transition" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                                @endif
                                @foreach ($columns as $column)
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 max-w-xs truncate" title="{{ $record->$column ?? '' }}">
                                        @if($record->$column === null)
                                            <span class="text-gray-400 italic">NULL</span>
                                        @elseif($column === 'password' || $column === 'default_password' || $column === 'remember_token')
                                            <span class="text-gray-400 italic">••••••••</span>
                                        @else
                                            {{ \Illuminate\Support\Str::limit((string)$record->$column, 50) }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columns) + ($isProtected ? 0 : 1) }}" class="px-6 py-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="bi bi-inbox text-4xl mb-3 text-gray-300"></i>
                                        <p>Tabel ini belum memiliki data.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $records->links('vendor.pagination.tailwind') }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search form validation
    var searchForm = document.getElementById('searchTableForm');
    if(searchForm) {
        searchForm.addEventListener('submit', function(e) {
            var searchColumn = document.getElementById('searchColumnSelect').value;
            var searchInput = document.getElementById('searchInput').value;

            if (searchInput.trim() !== '' && searchColumn === '') {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Kolom',
                    text: 'Kolom pencarian wajib dipilih jika Anda memasukkan kata kunci!',
                    confirmButtonColor: '#ec4899'
                });
            }
        });
    }
});

// =========================================================================
// CRUD ENGINE — Global Config
// =========================================================================
const TABLE_NAME = @json($table);
const PRIMARY_KEY = @json($primaryKey);
const IS_PROTECTED = @json($isProtected);
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const ROUTES = {
    columns: `/admin/database/${TABLE_NAME}/columns`,
    fkOptions: `/admin/database/${TABLE_NAME}/fk-options`,
    store: `/admin/database/${TABLE_NAME}`,
    getRow: (id) => `/admin/database/${TABLE_NAME}/${id}`,
    update: (id) => `/admin/database/${TABLE_NAME}/${id}`,
    destroy: (id) => `/admin/database/${TABLE_NAME}/${id}`,
};

// Cache for column metadata and FK options
let cachedColumns = null;
let cachedFkOptions = null;

// =========================================================================
// FETCH HELPERS
// =========================================================================
async function fetchColumns() {
    if (cachedColumns) return cachedColumns;
    const res = await fetch(ROUTES.columns, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN }
    });
    const data = await res.json();
    if (data.success) {
        cachedColumns = data.columns;
        return data.columns;
    }
    throw new Error(data.error || 'Gagal memuat metadata kolom');
}

async function fetchFkOptions() {
    if (cachedFkOptions) return cachedFkOptions;
    const res = await fetch(ROUTES.fkOptions, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN }
    });
    const data = await res.json();
    if (data.success) {
        cachedFkOptions = data.fkOptions;
        return data.fkOptions;
    }
    throw new Error(data.error || 'Gagal memuat opsi FK');
}

async function fetchRow(id) {
    const res = await fetch(ROUTES.getRow(id), {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN }
    });
    const data = await res.json();
    if (data.success) return data.data;
    throw new Error(data.error || 'Gagal memuat data');
}

// =========================================================================
// FORM BUILDER
// =========================================================================
function buildFormHtml(columns, fkOptions, existingData = null) {
    const isEdit = existingData !== null;
    let html = '<div style="max-height:65vh;overflow-y:auto;padding-right:8px;">';
    html += '<div style="display:grid;gap:16px;">';

    columns.forEach(col => {
        // Determine visibility
        if (!isEdit && col.is_hidden_create) return;
        const isReadonly = isEdit && col.is_readonly_edit;
        const value = existingData ? (existingData[col.name] ?? '') : (col.default_value ?? '');
        const required = !col.is_nullable && !col.is_primary && col.input_type !== 'boolean';

        html += `<div style="text-align:left;">`;
        html += `<label style="display:block;margin-bottom:4px;font-weight:600;font-size:13px;color:#374151;">`;
        html += `${col.name}`;
        if (required && !isReadonly) html += `<span style="color:#ef4444;margin-left:2px;">*</span>`;
        if (col.is_primary) html += `<span style="color:#9ca3af;font-weight:400;margin-left:4px;">(PK)</span>`;
        html += `</label>`;
        html += `<span style="display:block;font-size:11px;color:#9ca3af;margin-bottom:4px;">${col.data_type}${col.max_length ? '(' + col.max_length + ')' : ''}${col.is_nullable ? ' • nullable' : ''}</span>`;

        const inputId = `swal-input-${col.name}`;
        const disabledAttr = isReadonly ? 'disabled' : '';
        const inputStyle = `width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;transition:border-color 0.2s;outline:none;${isReadonly ? 'background:#f3f4f6;color:#6b7280;cursor:not-allowed;' : ''}`;
        const selectStyle = `width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;background:white;${isReadonly ? 'background:#f3f4f6;color:#6b7280;cursor:not-allowed;' : ''}`;

        switch (col.input_type) {
            case 'fk_select':
                const fk = fkOptions[col.name];
                html += `<select id="${inputId}" class="swal2-input-custom" style="${selectStyle}" ${disabledAttr}>`;
                html += `<option value="">-- Pilih ${col.name} --</option>`;
                if (fk && fk.options) {
                    fk.options.forEach(opt => {
                        const optVal = opt[fk.value_column];
                        const optLabel = opt[fk.label_column] || optVal;
                        const selected = String(value) === String(optVal) ? 'selected' : '';
                        html += `<option value="${escapeHtml(String(optVal))}" ${selected}>${escapeHtml(String(optLabel))} (${escapeHtml(String(optVal).substring(0, 8))}...)</option>`;
                    });
                }
                html += `</select>`;
                break;

            case 'enum_select':
                html += `<select id="${inputId}" class="swal2-input-custom" style="${selectStyle}" ${disabledAttr}>`;
                html += `<option value="">-- Pilih --</option>`;
                col.enum_values.forEach(ev => {
                    const selected = String(value) === String(ev) ? 'selected' : '';
                    html += `<option value="${escapeHtml(ev)}" ${selected}>${escapeHtml(ev)}</option>`;
                });
                html += `</select>`;
                break;

            case 'boolean':
                const boolVal = value === true || value === 't' || value === '1' || value === 1;
                html += `<select id="${inputId}" class="swal2-input-custom" style="${selectStyle}" ${disabledAttr}>`;
                html += `<option value="1" ${boolVal ? 'selected' : ''}>Ya (true)</option>`;
                html += `<option value="0" ${!boolVal ? 'selected' : ''}>Tidak (false)</option>`;
                html += `</select>`;
                break;

            case 'textarea':
                html += `<textarea id="${inputId}" class="swal2-input-custom" rows="3" style="${inputStyle}resize:vertical;" ${disabledAttr}>${escapeHtml(String(value || ''))}</textarea>`;
                break;

            case 'json':
                const jsonVal = (typeof value === 'object' && value !== null) ? JSON.stringify(value, null, 2) : String(value || '');
                html += `<textarea id="${inputId}" class="swal2-input-custom" rows="4" style="${inputStyle}resize:vertical;font-family:monospace;font-size:12px;" placeholder='{"key": "value"}' ${disabledAttr}>${escapeHtml(jsonVal)}</textarea>`;
                break;

            case 'password':
                html += `<input id="${inputId}" type="password" class="swal2-input-custom" style="${inputStyle}" placeholder="${isEdit ? '(kosongkan jika tidak diubah)' : 'Masukkan password'}" ${disabledAttr}>`;
                break;

            case 'date':
                const dateVal = value ? String(value).substring(0, 10) : '';
                html += `<input id="${inputId}" type="date" class="swal2-input-custom" style="${inputStyle}" value="${escapeHtml(dateVal)}" ${disabledAttr}>`;
                break;

            case 'datetime':
                let dtVal = '';
                if (value) {
                    try {
                        const d = new Date(value);
                        if (!isNaN(d.getTime())) {
                            dtVal = d.toISOString().slice(0, 16);
                        }
                    } catch(e) {}
                }
                html += `<input id="${inputId}" type="datetime-local" class="swal2-input-custom" style="${inputStyle}" value="${escapeHtml(dtVal)}" ${disabledAttr}>`;
                break;

            case 'time':
                html += `<input id="${inputId}" type="time" class="swal2-input-custom" style="${inputStyle}" value="${escapeHtml(String(value || ''))}" ${disabledAttr}>`;
                break;

            case 'number':
                html += `<input id="${inputId}" type="number" class="swal2-input-custom" style="${inputStyle}" value="${escapeHtml(String(value || ''))}" step="1" ${disabledAttr}>`;
                break;

            case 'decimal':
                html += `<input id="${inputId}" type="number" class="swal2-input-custom" style="${inputStyle}" value="${escapeHtml(String(value || ''))}" step="any" ${disabledAttr}>`;
                break;

            default: // text
                html += `<input id="${inputId}" type="text" class="swal2-input-custom" style="${inputStyle}" value="${escapeHtml(String(value || ''))}" ${col.max_length ? `maxlength="${col.max_length}"` : ''} ${disabledAttr}>`;
                break;
        }

        html += `</div>`;
    });

    html += '</div></div>';
    return html;
}

function collectFormData(columns, isEdit = false) {
    const data = {};

    columns.forEach(col => {
        if (!isEdit && col.is_hidden_create) return;
        if (isEdit && col.is_readonly_edit) return;

        const inputEl = document.getElementById(`swal-input-${col.name}`);
        if (!inputEl) return;

        let val = inputEl.value;

        // For password in edit mode: skip if empty
        if (isEdit && col.input_type === 'password' && val === '') return;

        data[col.name] = val;
    });

    return data;
}

// =========================================================================
// CREATE MODAL
// =========================================================================
async function openCreateModal() {
    if (IS_PROTECTED) return;

    Swal.fire({
        title: 'Memuat form...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    try {
        const [columns, fkOptions] = await Promise.all([fetchColumns(), fetchFkOptions()]);
        const formHtml = buildFormHtml(columns, fkOptions, null);

        const result = await Swal.fire({
            title: `<i class="bi bi-plus-circle text-green-500"></i> Tambah Data`,
            html: formHtml,
            width: '700px',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-save mr-1"></i> Simpan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#22c55e',
            cancelButtonColor: '#6b7280',
            focusConfirm: false,
            customClass: {
                popup: 'swal-crud-popup',
                htmlContainer: 'swal-crud-container',
            },
            preConfirm: () => {
                const data = collectFormData(columns, false);
                return data;
            }
        });

        if (result.isConfirmed && result.value) {
            await submitCreate(result.value);
        }
    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: err.message || 'Gagal memuat form.',
            confirmButtonColor: '#ec4899'
        });
    }
}

async function submitCreate(formData) {
    Swal.fire({
        title: 'Menyimpan...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    try {
        const res = await fetch(ROUTES.store, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
            },
            body: JSON.stringify(formData),
        });

        const data = await res.json();

        if (data.success) {
            await Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message || 'Data berhasil ditambahkan.',
                confirmButtonColor: '#22c55e',
                timer: 2000,
                timerProgressBar: true,
            });
            window.location.reload();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: data.error || 'Terjadi kesalahan saat menyimpan data.',
                confirmButtonColor: '#ec4899'
            });
        }
    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: err.message || 'Terjadi kesalahan jaringan.',
            confirmButtonColor: '#ec4899'
        });
    }
}

// =========================================================================
// EDIT MODAL
// =========================================================================
async function openEditModal(id) {
    if (IS_PROTECTED || !id) return;

    Swal.fire({
        title: 'Memuat data...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    try {
        const [columns, fkOptions, rowData] = await Promise.all([
            fetchColumns(),
            fetchFkOptions(),
            fetchRow(id)
        ]);

        const formHtml = buildFormHtml(columns, fkOptions, rowData);

        const result = await Swal.fire({
            title: `<i class="bi bi-pencil-square text-blue-500"></i> Edit Data`,
            html: formHtml,
            width: '700px',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-save mr-1"></i> Simpan Perubahan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#6b7280',
            focusConfirm: false,
            customClass: {
                popup: 'swal-crud-popup',
                htmlContainer: 'swal-crud-container',
            },
            preConfirm: () => {
                const data = collectFormData(columns, true);
                return data;
            }
        });

        if (result.isConfirmed && result.value) {
            await submitUpdate(id, result.value);
        }
    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: err.message || 'Gagal memuat data.',
            confirmButtonColor: '#ec4899'
        });
    }
}

async function submitUpdate(id, formData) {
    Swal.fire({
        title: 'Memperbarui...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    try {
        const res = await fetch(ROUTES.update(id), {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
            },
            body: JSON.stringify(formData),
        });

        const data = await res.json();

        if (data.success) {
            await Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message || 'Data berhasil diperbarui.',
                confirmButtonColor: '#3b82f6',
                timer: 2000,
                timerProgressBar: true,
            });
            window.location.reload();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: data.error || 'Terjadi kesalahan saat memperbarui data.',
                confirmButtonColor: '#ec4899'
            });
        }
    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: err.message || 'Terjadi kesalahan jaringan.',
            confirmButtonColor: '#ec4899'
        });
    }
}

// =========================================================================
// DELETE CONFIRMATION
// =========================================================================
async function confirmDelete(id) {
    if (IS_PROTECTED || !id) return;

    const result = await Swal.fire({
        title: '<i class="bi bi-exclamation-triangle text-red-500"></i> Hapus Data?',
        html: `<p class="text-gray-600">Anda yakin ingin menghapus data ini?</p><p class="text-sm text-gray-400 mt-2 font-mono">${PRIMARY_KEY}: ${escapeHtml(id)}</p><p class="text-sm text-red-500 mt-2 font-semibold">Tindakan ini tidak dapat dibatalkan!</p>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-trash mr-1"></i> Ya, Hapus!',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
    });

    if (result.isConfirmed) {
        await submitDelete(id);
    }
}

async function submitDelete(id) {
    Swal.fire({
        title: 'Menghapus...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    try {
        const res = await fetch(ROUTES.destroy(id), {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
            },
        });

        const data = await res.json();

        if (data.success) {
            await Swal.fire({
                icon: 'success',
                title: 'Terhapus!',
                text: data.message || 'Data berhasil dihapus.',
                confirmButtonColor: '#22c55e',
                timer: 2000,
                timerProgressBar: true,
            });
            window.location.reload();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: data.error || 'Terjadi kesalahan saat menghapus data.',
                confirmButtonColor: '#ec4899'
            });
        }
    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: err.message || 'Terjadi kesalahan jaringan.',
            confirmButtonColor: '#ec4899'
        });
    }
}

// =========================================================================
// UTILITY
// =========================================================================
function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}
</script>

<style>
    .swal-crud-popup {
        font-family: inherit !important;
    }
    .swal-crud-container {
        text-align: left !important;
    }
    .swal2-input-custom:focus {
        border-color: #ec4899 !important;
        box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.15) !important;
    }
</style>
@endpush
@endsection
