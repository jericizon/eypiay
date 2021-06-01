<?php

namespace Eypiay\Eypiay\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use DB;
use Carbon\Carbon;

use Eypiay\Eypiay\Traits\HelperTrait as EypiayHelper;

class EypiayController extends EypiayBaseController
{
    use EypiayHelper;

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

        $filterColumns = $request->input('filter', '');
        $this->_eypiayFilterColumns(explode(self::PARAM_SPLITTER, $filterColumns));

        $orderColumn = $request->input('order', '');
        $this->_eypiayOrderColumn($orderColumn);

        $searchColumns = $request->input('search', '');
        $strictSearch = filter_var($request->input('strict_search', false), FILTER_VALIDATE_BOOLEAN);
        $this->_eypiaySearchColumns(explode(self::PARAM_SPLITTER, $searchColumns), $strictSearch);

        $items = (int) $request->input('items', config('eypiay.MIN_QUERY'));

        $this->response->result = $this->_paginate($items);
        $this->success = true;
        return $this->eypiayReturn();
    }

    private function _eypiayFilterColumns(array $filterColumns = [])
    {
        $filterColumns = array_filter($filterColumns);

        $columns = $this->getVisibleColumns($this->dbTable, $this->dbHidden);

        if (count($filterColumns) > 0) {
            $columns = array_intersect($columns, $filterColumns);
            $this->response->params['select'] = $columns;
        }

        $this->query->select($columns);
    }

    private function _eypiayOrderColumn(string $orderColumn)
    {

        if (!$orderColumn) {
            return;
        }

        $columns = $this->getVisibleColumns($this->dbTable, $this->dbHidden);

        $order = explode(':', $orderColumn);
        $orderColumnName = $order[0] ?? 'id';
        $condition = $order[1] ?? '';

        if (!in_array($orderColumnName, $columns)) {
            return;
        }

        if (!in_array($condition, ['asc', 'desc'])) {
            $condition = 'asc';
        }

        $this->response->params['order'] = [
            'order' => $orderColumnName,
            'order_by' => $condition
        ];
        $this->query->orderBy($orderColumnName, $condition);
    }

    private function _eypiaySearchColumns(array $searchColumns = [], bool $strictSearch)
    {
        $searchColumns = array_filter($searchColumns);

        $columns = $this->getVisibleColumns($this->dbTable, $this->dbHidden);

        $searchCollection = collect($searchColumns)->map(function ($search) use ($columns) {
            $item = explode(':', $search);
            $key = $item[0];
            $value = $item[1] ?? 'asc';

            if (!in_array($key, $columns)) {
                return null;
            }
            return ['key' => $key, 'value' => $value];
        })->toArray();


        $searchCollection = array_filter($searchCollection);

        if (count($searchCollection) === 0) {
            return;
        }

        $this->response->params['search'] = [
            'strict' => $strictSearch,
            'search' => $searchCollection,
        ];

        $this->query->where(function ($query) use ($searchCollection, $strictSearch) {
            foreach ($searchCollection as $search) {
                $searchKey = $search['key'];
                $searchValue = $search['value'];

                if ($strictSearch) {
                    $query->where($searchKey, $searchValue);
                } else {
                    $query->orWhere($searchKey, 'LIKE', "%{$searchValue}%");
                }
            }
        });

        // $this->query->select($columns);
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
        $this->response->params['items'] = $items;
        return $this->query->paginate($items);
    }

    public function post(Request $request)
    {
        $this->_initDbConnection();

        if (!$this->query) {
            return $this->eypiayReturn();
        }

        if (count($this->requestValidation)) {
            $validator = Validator::make($request->all(), $this->requestValidation);
            if ($validator->fails()) {
                $this->code = 422;
                $this->response->errors = $validator->errors();
                return $this->eypiayReturn();
            }
        }

        $columns = $this->getFillableColumns($this->dbTable);

        $post = $request->only($columns);

        if (count($this->requestCasts)) {
            foreach ($this->requestCasts as $castName => $cast) {
                if (isset($post[$castName])) {
                    $post[$castName] = $this->castData($cast, $post[$castName]);
                }
            }
        }

        if (in_array('created_at', $columns) && !isset($post['created_at'])) {
            $post['created_at'] = Carbon::now();
        }

        if (in_array('updated_at', $columns) && !isset($post['updated_at'])) {
            $post['updated_at'] = Carbon::now();
        }

        DB::beginTransaction();
        try {
            $createdRecord = DB::table($this->dbTable)->insertGetId($post);

            if (!$createdRecord) {
                $this->code = 422;
                $this->response->message = 'Failed to insert new record.';
            } else {
                $this->success = true;
                $this->response->message = 'New record added.';

                $columns = $this->getVisibleColumns($this->dbTable, $this->dbHidden);

                $this->response->result = DB::table($this->dbTable)
                    ->select($columns)
                    ->where('id', $createdRecord)
                    ->first();

                DB::commit();
            }
        } catch (\Exception $error) {
            DB::rollBack();
            $this->code = 422;
            $this->response->message = $error->getMessage();
        }

        return $this->eypiayReturn();
    }
}
