<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseController extends Controller
{
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

    public function show($table)
    {
        // Prevent SQL injection by verifying the table actually exists
        if (!Schema::hasTable($table)) {
            abort(404, 'Tabel tidak ditemukan.');
        }

        // Get columns
        $columns = Schema::getColumnListing($table);

        // Get paginated data
        $records = DB::table($table)->paginate(50);

        return view('admin.database.show', compact('table', 'columns', 'records'));
    }
}
