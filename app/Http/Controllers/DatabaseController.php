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

        return view('admin.database.show', compact('table', 'columns', 'records', 'search', 'searchColumn'));
    }
}
