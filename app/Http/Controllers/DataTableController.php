<?php

namespace App\Http\Controllers;

use App\DataTables\CategoriesDataTable;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Facades\DataTables;
use Yajra\DataTables\QueryDataTable;

class DataTableController extends Controller
{
    public function getPosts(CategoriesDataTable $dataTable)
    {
        return $dataTable->render('datatable.index');
        return view('datatable.index');
    }

    public function getTableType($type)
    {
        if ($type=='viaFactory'){
            return $this->viaFactory();
        }
        if ($type=='viaFacade'){
            return $this->viaFacade();
        }
        if ($type=='viaDependencyInjection'){
            return $this->viaDependencyInjection();
        }
        if ($type=='viaIoC'){
            return $this->viaIoC();
        }
        if ($type=='viaEloquentNewInstance'){
            return $this->viaEloquentNewInstance();
        }
        if ($type=='EF'){
            return $this->viaFactory();
        }
    }

    public function viaFactory()
    {
        $model = Category::query();

        return DataTables::of($model)->toJson();
    }
    public function viaFacade()
    {
        $model = Category::query();

        return DataTables::eloquent($model)->toJson();
    }
    public function viaDependencyInjection(DataTables $dataTables)
    {
        $model = Category::query();

        return $dataTables->eloquent($model)->toJson();
    }
    public function viaIoC(DataTables $dataTables)
    {
        $model = Category::query();

        return app('datatables')->eloquent($model)->toJson();
    }
    public function viaEloquentNewInstance()
    {
        $model = Category::query();

        return (new EloquentDataTable($model))->toJson();
    }

    public function queryViaFactory()
    {
        $query = DB::table('categories');

        return DataTables::of($query)->toJson();
    }
    public function queryViaFacade()
    {
        $query = DB::table('categories');

        return DataTables::queryBuilder($query)->toJson();
    }
    public function queryViaDI(DataTables $dataTables)
    {
        $query = DB::table('categories');

        return $dataTables->queryBuilder($query)->toJson();
    }
    public function queryViaIoC()
    {
        $query = DB::table('categories');

        return app('datatables')->queryBuilder($query)->toJson();
    }
    public function queryViaNewInstance()
    {
        $query = DB::table('categories');

        return (new QueryDataTable($query))->toJson();
    }
}
