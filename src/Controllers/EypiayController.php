<?php

namespace Eypiay\Eypiay\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use DB;
use Eypiay\Eypiay\Eypiay;

class EypiayController extends EypiayBaseController
{
    const PARAM_SPLITTER = '|';

    protected $query;

    private function _initDbConnection()
    {
        if (!$this->dbTable || !Schema::hasTable($this->dbTable)) {
            return;
        }
        $this->query = DB::table($this->dbTable);
    }

    public function get(Request $request)
    {
        $this->_initDbConnection();

        if (!$this->query) {
            return $this->eypiayReturn();
        }

        $filterColumns = strtolower($request->input('filter', ''));

        $this->_eypiayFilterColumns(explode(self::PARAM_SPLITTER, $filterColumns));

        $items = (int) $request->input('items', config('eypiay.MIN_QUERY'));

        $this->response->result = $this->_paginate($items);

        $this->success = true;

        return $this->eypiayReturn();
    }

    private function _eypiayFilterColumns(array $filterColumns = [])
    {
        $filterColumns = array_filter($filterColumns);

        $columns = Schema::getColumnListing($this->dbTable);
        if (count($this->dbHidden)) {
            $columns = array_diff($columns, $this->dbHidden);
        }

        if (count($filterColumns) > 0) {
            $columns = array_intersect($columns, $filterColumns);
        }

        $this->query->select($columns);
    }

    private function _paginate(int $items = 0)
    {
        if ($items < config('eypiay.MIN_QUERY')) {
            // reached minimum item query;
            $items = config('eypiay.MIN_QUERY');
        }

        if ($items > config('eypiay.MAX_QUERY')) {
            // reached maximium item query
            $items = config('eypiay.MAX_QUERY');
        }

        return $this->query->paginate($items);
    }
}
