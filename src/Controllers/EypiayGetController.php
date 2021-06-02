<?php

namespace Eypiay\Eypiay\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use DB;
use Carbon\Carbon;

use Eypiay\Eypiay\Traits\HelperTrait as EypiayHelper;

class EypiayGetController extends EypiayBaseController
{
    use EypiayHelper;

    public function get(Request $request)
    {
        $filterColumns = $request->input('filter', '');
        $this->_eypiayFilterColumns(explode(config('eypiay.param_splitter'), $filterColumns));

        $orderColumn = $request->input('order', '');
        $this->_eypiayOrderColumn($orderColumn);

        $searchColumns = $request->input('search', '');
        $strictSearch = filter_var($request->input('strict_search', false), FILTER_VALIDATE_BOOLEAN);
        $this->_eypiaySearchColumns(explode(config('eypiay.param_splitter'), $searchColumns), $strictSearch);

        $items = (int) $request->input('items', config('eypiay.min_query'));

        $this->response->result = $this->_paginate($items);
        $this->success = true;
        return $this->eypiayReturn();
    }

    private function _eypiayFilterColumns(array $filterColumns = [])
    {
        $filterColumns = array_filter($filterColumns);

        $visibleColumns = $this->getVisibleColumns($this->dbTable, $this->dbHidden);

        if (count($filterColumns) > 0) {
            $visibleColumns = array_intersect($visibleColumns, $filterColumns);
            $this->response->params['select'] = $visibleColumns;
        }

        $this->query->select($visibleColumns);
    }

    private function _eypiayOrderColumn(string $orderColumn)
    {

        if (!$orderColumn) {
            return;
        }

        $visibleColumns = $this->getVisibleColumns($this->dbTable, $this->dbHidden);

        $order = explode(':', $orderColumn);
        $orderColumnName = $order[0] ?? 'id';
        $condition = $order[1] ?? '';

        if (!in_array($orderColumnName, $visibleColumns)) {
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

        $visibleColumns = $this->getVisibleColumns($this->dbTable, $this->dbHidden);

        $searchCollection = collect($searchColumns)->map(function ($search) use ($visibleColumns) {
            $item = explode(':', $search);
            $key = $item[0];
            $value = $item[1] ?? 'asc';

            if (!in_array($key, $visibleColumns)) {
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
    }

    private function _paginate(int $items = 0)
    {
        // reached minimum item query;
        if ($items < config('eypiay.min_query')) {
            $items = config('eypiay.min_query');
        }

        // reached maximium item query
        if ($items > config('eypiay.max_query')) {
            $items = config('eypiay.max_query');
        }

        $this->response->params['items'] = $items;
        return $this->query->paginate($items);
    }
}
