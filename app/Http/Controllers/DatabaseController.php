<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseController extends Controller
{
    /**
     * Tabel yang tidak boleh di-CRUD (hanya sistem internal Laravel/framework).
     */
    protected $protectedTables = [
        'migrations',
        'sessions',
        'cache',
        'cache_locks',
        'password_reset_tokens',
        'jobs',
    ];

    /**
     * Halaman daftar semua tabel database.
     */
    public function index()
    {
        // Get all tables in the database
        $tables = Schema::getTables();

        $tableList = [];
        foreach ($tables as $table) {
            $tableName = $table['name'];

            // Count rows
            $rowCount = DB::table($tableName)->count();

            $tableList[] = [
                'name' => $tableName,
                'rows' => $rowCount,
            ];
        }

        return view('admin.database.index', compact('tableList'));
    }

    /**
     * Halaman detail tabel: menampilkan data + kolom aksi CRUD.
     */
    public function show(Request $request, $table)
    {
        // Prevent SQL injection by verifying the table actually exists
        if (!Schema::hasTable($table)) {
            abort(404, 'Tabel tidak ditemukan.');
        }

        // Get columns
        $columns = Schema::getColumnListing($table);

        $search = $request->query('search');
        $searchColumn = $request->query('search_column');

        $query = DB::table($table);

        if (!empty($search) && !empty($searchColumn) && in_array($searchColumn, $columns)) {
            $query->where($searchColumn, 'like', '%' . $search . '%');
        }

        // Get paginated data
        $records = $query->paginate(10)->withQueryString();

        // Detect primary key for CRUD operations
        $primaryKey = $this->getPrimaryKey($table);

        // Check if table is protected (no CRUD)
        $isProtected = in_array($table, $this->protectedTables);

        return view('admin.database.show', compact('table', 'columns', 'records', 'search', 'searchColumn', 'primaryKey', 'isProtected'));
    }

    /**
     * API: Mengembalikan metadata kolom untuk generate form dinamis.
     */
    public function getColumns($table)
    {
        if (!Schema::hasTable($table)) {
            return response()->json(['error' => 'Tabel tidak ditemukan.'], 404);
        }

        $columns = $this->getColumnMetadata($table);
        $primaryKey = $this->getPrimaryKey($table);

        return response()->json([
            'success' => true,
            'columns' => $columns,
            'primaryKey' => $primaryKey,
        ]);
    }

    /**
     * API: Mengembalikan 1 baris data untuk prefill form edit.
     */
    public function getRow($table, $id)
    {
        if (!Schema::hasTable($table)) {
            return response()->json(['error' => 'Tabel tidak ditemukan.'], 404);
        }

        $primaryKey = $this->getPrimaryKey($table);
        $row = DB::table($table)->where($primaryKey, $id)->first();

        if (!$row) {
            return response()->json(['error' => 'Data tidak ditemukan.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $row,
        ]);
    }

    /**
     * API: Mengembalikan opsi dropdown untuk kolom FK.
     */
    public function getForeignKeyOptions($table)
    {
        if (!Schema::hasTable($table)) {
            return response()->json(['error' => 'Tabel tidak ditemukan.'], 404);
        }

        $columns = $this->getColumnMetadata($table);
        $fkOptions = [];

        foreach ($columns as $col) {
            if ($col['input_type'] === 'fk_select' && $col['fk_table']) {
                $fkTable = $col['fk_table'];
                $fkColumn = $col['fk_column'];
                $fkLabelColumn = $col['fk_label_column'];

                if (Schema::hasTable($fkTable)) {
                    $options = DB::table($fkTable)
                        ->select($fkColumn, $fkLabelColumn)
                        ->orderBy($fkLabelColumn)
                        ->limit(500)
                        ->get();

                    $fkOptions[$col['name']] = [
                        'table' => $fkTable,
                        'value_column' => $fkColumn,
                        'label_column' => $fkLabelColumn,
                        'options' => $options,
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'fkOptions' => $fkOptions,
        ]);
    }

    /**
     * Menyimpan data baru ke tabel.
     */
    public function store(Request $request, $table)
    {
        if (!Schema::hasTable($table)) {
            return response()->json(['error' => 'Tabel tidak ditemukan.'], 404);
        }

        if (in_array($table, $this->protectedTables)) {
            return response()->json(['error' => 'Tabel ini dilindungi dan tidak dapat dimodifikasi.'], 403);
        }

        try {
            $columns = $this->getColumnMetadata($table);
            $primaryKey = $this->getPrimaryKey($table);
            $data = [];

            foreach ($columns as $col) {
                $colName = $col['name'];

                // Skip auto-generated columns
                if (in_array($colName, ['created_at', 'updated_at'])) {
                    continue;
                }

                // Handle UUID primary key — generate automatically
                if ($colName === $primaryKey && $col['data_type'] === 'uuid') {
                    $data[$colName] = (string) Str::uuid();
                    continue;
                }

                // Skip auto-increment PK
                if ($colName === $primaryKey && $col['is_auto_increment']) {
                    continue;
                }

                if ($request->has($colName)) {
                    $value = $request->input($colName);

                    // Handle empty strings as NULL for nullable columns
                    if ($value === '' && $col['is_nullable']) {
                        $value = null;
                    }

                    // Hash password columns
                    if ($colName === 'password' && $value !== null && $value !== '') {
                        $value = Hash::make($value);
                    }

                    // Cast boolean
                    if ($col['input_type'] === 'boolean') {
                        $value = $value === '1' || $value === 'true' || $value === true ? true : false;
                    }

                    $data[$colName] = $value;
                }
            }

            // Add timestamps
            $columnNames = Schema::getColumnListing($table);
            $now = now();
            if (in_array('created_at', $columnNames)) {
                $data['created_at'] = $now;
            }
            if (in_array('updated_at', $columnNames)) {
                $data['updated_at'] = $now;
            }

            DB::table($table)->insert($data);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil ditambahkan.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal menambahkan data: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Memperbarui data yang ada di tabel.
     */
    public function update(Request $request, $table, $id)
    {
        if (!Schema::hasTable($table)) {
            return response()->json(['error' => 'Tabel tidak ditemukan.'], 404);
        }

        if (in_array($table, $this->protectedTables)) {
            return response()->json(['error' => 'Tabel ini dilindungi dan tidak dapat dimodifikasi.'], 403);
        }

        try {
            $columns = $this->getColumnMetadata($table);
            $primaryKey = $this->getPrimaryKey($table);
            $data = [];

            foreach ($columns as $col) {
                $colName = $col['name'];

                // Skip PK and auto-generated columns
                if ($colName === $primaryKey || in_array($colName, ['created_at', 'updated_at'])) {
                    continue;
                }

                if ($request->has($colName)) {
                    $value = $request->input($colName);

                    // Handle empty strings as NULL for nullable columns
                    if ($value === '' && $col['is_nullable']) {
                        $value = null;
                    }

                    // Hash password columns (only if provided)
                    if ($colName === 'password') {
                        if ($value === null || $value === '') {
                            continue; // Don't update password if empty
                        }
                        $value = Hash::make($value);
                    }

                    // Cast boolean
                    if ($col['input_type'] === 'boolean') {
                        $value = $value === '1' || $value === 'true' || $value === true ? true : false;
                    }

                    $data[$colName] = $value;
                }
            }

            // Update timestamp
            $columnNames = Schema::getColumnListing($table);
            if (in_array('updated_at', $columnNames)) {
                $data['updated_at'] = now();
            }

            DB::table($table)->where($primaryKey, $id)->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal memperbarui data: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Menghapus data dari tabel.
     */
    public function destroy($table, $id)
    {
        if (!Schema::hasTable($table)) {
            return response()->json(['error' => 'Tabel tidak ditemukan.'], 404);
        }

        if (in_array($table, $this->protectedTables)) {
            return response()->json(['error' => 'Tabel ini dilindungi dan tidak dapat dimodifikasi.'], 403);
        }

        try {
            $primaryKey = $this->getPrimaryKey($table);
            $deleted = DB::table($table)->where($primaryKey, $id)->delete();

            if ($deleted === 0) {
                return response()->json(['error' => 'Data tidak ditemukan.'], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal menghapus data: ' . $e->getMessage(),
            ], 422);
        }
    }

    // =========================================================================
    // PRIVATE HELPER METHODS
    // =========================================================================

    /**
     * Deteksi primary key tabel (support PostgreSQL).
     */
    private function getPrimaryKey($table)
    {
        try {
            $result = DB::select("
                SELECT a.attname AS column_name
                FROM pg_index i
                JOIN pg_attribute a ON a.attrelid = i.indrelid AND a.attnum = ANY(i.indkey)
                WHERE i.indrelid = ?::regclass
                AND i.indisprimary
            ", [$table]);

            if (!empty($result)) {
                return $result[0]->column_name;
            }
        } catch (\Exception $e) {
            // Fallback
        }

        // Fallback: assume 'id'
        return 'id';
    }

    /**
     * Mendapatkan metadata detail untuk semua kolom pada tabel.
     * Termasuk: tipe data, nullable, default value, enum values, FK info.
     */
    private function getColumnMetadata($table)
    {
        $columnNames = Schema::getColumnListing($table);
        $primaryKey = $this->getPrimaryKey($table);
        $columns = [];

        // Get detailed column info from information_schema (PostgreSQL)
        $columnInfos = DB::select("
            SELECT
                c.column_name,
                c.data_type,
                c.udt_name,
                c.character_maximum_length,
                c.is_nullable,
                c.column_default,
                c.numeric_precision,
                c.numeric_scale
            FROM information_schema.columns c
            WHERE c.table_name = ?
            AND c.table_schema = 'public'
            ORDER BY c.ordinal_position
        ", [$table]);

        $columnInfoMap = [];
        foreach ($columnInfos as $info) {
            $columnInfoMap[$info->column_name] = $info;
        }

        // Get FK constraints
        $fkConstraints = DB::select("
            SELECT
                kcu.column_name,
                ccu.table_name AS foreign_table_name,
                ccu.column_name AS foreign_column_name
            FROM information_schema.table_constraints tc
            JOIN information_schema.key_column_usage kcu
                ON tc.constraint_name = kcu.constraint_name
                AND tc.table_schema = kcu.table_schema
            JOIN information_schema.constraint_column_usage ccu
                ON ccu.constraint_name = tc.constraint_name
                AND ccu.table_schema = tc.table_schema
            WHERE tc.constraint_type = 'FOREIGN KEY'
            AND tc.table_name = ?
            AND tc.table_schema = 'public'
        ", [$table]);

        $fkMap = [];
        foreach ($fkConstraints as $fk) {
            $fkMap[$fk->column_name] = [
                'table' => $fk->foreign_table_name,
                'column' => $fk->foreign_column_name,
            ];
        }

        // Get enum values for USER-DEFINED types (PostgreSQL uses CHECK constraints or custom types)
        $enumValues = $this->getEnumValues($table);

        foreach ($columnNames as $colName) {
            $info = $columnInfoMap[$colName] ?? null;
            $dataType = $info ? $info->data_type : 'text';
            $udtName = $info ? $info->udt_name : '';
            $isNullable = $info ? ($info->is_nullable === 'YES') : true;
            $defaultValue = $info ? $info->column_default : null;
            $maxLength = $info ? $info->character_maximum_length : null;

            // Determine if auto-increment (serial/bigserial in pg or nextval() default)
            $isAutoIncrement = $defaultValue && str_contains($defaultValue, 'nextval(');

            // Determine input type
            $inputType = $this->determineInputType($colName, $dataType, $udtName, $fkMap, $enumValues);

            $column = [
                'name' => $colName,
                'data_type' => $udtName ?: $dataType,
                'input_type' => $inputType,
                'is_nullable' => $isNullable,
                'is_primary' => ($colName === $primaryKey),
                'is_auto_increment' => $isAutoIncrement,
                'default_value' => $defaultValue,
                'max_length' => $maxLength,
                'enum_values' => $enumValues[$colName] ?? [],
                'fk_table' => isset($fkMap[$colName]) ? $fkMap[$colName]['table'] : null,
                'fk_column' => isset($fkMap[$colName]) ? $fkMap[$colName]['column'] : null,
                'fk_label_column' => null,
                'is_hidden_create' => false,
                'is_readonly_edit' => false,
            ];

            // Set FK label column (find a sensible label column in the FK table)
            if ($column['fk_table']) {
                $column['fk_label_column'] = $this->findLabelColumn($column['fk_table'], $column['fk_column']);
            }

            // Mark hidden/readonly columns
            if ($colName === $primaryKey) {
                $column['is_hidden_create'] = true;
                $column['is_readonly_edit'] = true;
            }
            if (in_array($colName, ['created_at', 'updated_at'])) {
                $column['is_hidden_create'] = true;
                $column['is_readonly_edit'] = true;
            }
            if ($colName === 'remember_token') {
                $column['is_hidden_create'] = true;
                $column['is_readonly_edit'] = true;
            }

            $columns[] = $column;
        }

        return $columns;
    }

    /**
     * Menentukan input type HTML berdasarkan tipe kolom database.
     */
    private function determineInputType($colName, $dataType, $udtName, $fkMap, $enumValues)
    {
        // FK columns → searchable dropdown
        if (isset($fkMap[$colName])) {
            return 'fk_select';
        }

        // Enum columns
        if (isset($enumValues[$colName]) && !empty($enumValues[$colName])) {
            return 'enum_select';
        }

        // Password column
        if ($colName === 'password' || $colName === 'default_password') {
            return 'password';
        }

        // Boolean
        if ($udtName === 'bool' || $dataType === 'boolean') {
            return 'boolean';
        }

        // Date/Time
        if (in_array($dataType, ['date'])) {
            return 'date';
        }
        if (in_array($dataType, ['timestamp without time zone', 'timestamp with time zone', 'timestamp'])) {
            return 'datetime';
        }
        if ($dataType === 'time without time zone' || $dataType === 'time') {
            return 'time';
        }

        // Numeric
        if (in_array($dataType, ['integer', 'bigint', 'smallint'])) {
            return 'number';
        }
        if (in_array($dataType, ['numeric', 'decimal', 'real', 'double precision'])) {
            return 'decimal';
        }

        // Text / large text
        if (in_array($dataType, ['text'])) {
            return 'textarea';
        }

        // JSON
        if (in_array($dataType, ['json', 'jsonb'])) {
            return 'json';
        }

        // UUID
        if ($udtName === 'uuid') {
            return 'text';
        }

        // Default: text input
        return 'text';
    }

    /**
     * Mendapatkan enum values dari PostgreSQL CHECK constraints.
     * PostgreSQL tidak punya tipe ENUM native—Laravel menggunakan CHECK constraints.
     */
    private function getEnumValues($table)
    {
        $enumValues = [];

        try {
            // Get CHECK constraints yang berisi ANY (ARRAY[...])
            $constraints = DB::select("
                SELECT
                    conname,
                    pg_get_constraintdef(oid) AS constraint_def
                FROM pg_constraint
                WHERE conrelid = ?::regclass
                AND contype = 'c'
            ", [$table]);

            foreach ($constraints as $constraint) {
                $def = $constraint->constraint_def;

                // Match pattern: CHECK ((column_name)::text = ANY ((ARRAY['val1', 'val2'])::text[]))
                // or simpler patterns
                if (preg_match('/\(\((\w+)\)::text\s*=\s*ANY/', $def, $colMatch)) {
                    $columnName = $colMatch[1];

                    // Extract all quoted values
                    if (preg_match_all("/'([^']+)'/", $def, $valMatches)) {
                        $values = [];
                        foreach ($valMatches[1] as $val) {
                            // Skip ::text suffixes captured accidentally
                            if ($val !== 'text') {
                                $values[] = $val;
                            }
                        }
                        if (!empty($values)) {
                            $enumValues[$columnName] = $values;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail — enum detection is best-effort
        }

        return $enumValues;
    }

    /**
     * Menemukan kolom label yang paling tepat untuk FK dropdown.
     * Prioritas: nama*, name*, title*, label*, slug*, key*, lalu PK.
     */
    private function findLabelColumn($fkTable, $fkPkColumn)
    {
        if (!Schema::hasTable($fkTable)) {
            return $fkPkColumn;
        }

        $cols = Schema::getColumnListing($fkTable);

        // Priority patterns for label columns (Indonesian and English)
        $patterns = ['nama_', 'name', 'title', 'label', 'slug', 'key', 'email', 'deskripsi', 'description'];

        foreach ($patterns as $pattern) {
            foreach ($cols as $col) {
                if (str_contains(strtolower($col), $pattern) && $col !== $fkPkColumn) {
                    return $col;
                }
            }
        }

        // Fallback: use the PK column itself
        return $fkPkColumn;
    }
}
