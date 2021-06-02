<?php

namespace Eypiay\Eypiay\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;


trait HelperTrait
{
    protected function getFillableColumns(string $dbTable)
    {
        try {
            return Schema::getColumnListing($dbTable);
        } catch (\Exception $error) {
            \Log::error($error);
            return [];
        }
    }

    protected function getVisibleColumns(string $dbTable, array $hiddenColumns = [])
    {
        $columns = $this->getFillableColumns($dbTable);
        if (count($hiddenColumns)) {
            $columns = array_diff($columns, $hiddenColumns);
        }
        return $columns;
    }

    /**
     * Supported casts:
     * hash
     */
    protected function castData(string $cast, $item)
    {

        if (strtolower($cast) === 'hash') {
            return Hash::make($item);
        }

        return $cast;
    }

    protected function validateRequests(Request $request, array $requestValidation = [])
    {
        if (count($requestValidation) === 0) {
            return;
        }

        $validator = Validator::make($request->all(), $requestValidation);

        if (!$validator->fails()) {
            return;
        }

        return (object) [
            'errors' => $validator->errors()
        ];
    }

    protected function initPostInserts(string $dbTable, Request $request, array $requestCasts = [])
    {
        $fillableColumns = $this->getFillableColumns($dbTable);
        $post = $request->only($fillableColumns);

        if (count($requestCasts)) {
            foreach ($requestCasts as $castName => $cast) {
                if (isset($post[$castName])) {
                    $post[$castName] = $this->castData($cast, $post[$castName]);
                }
            }
        }

        if (in_array('created_at', $fillableColumns) && !isset($post['created_at'])) {
            $post['created_at'] = Carbon::now();
        }

        if (in_array('updated_at', $fillableColumns) && !isset($post['updated_at'])) {
            $post['updated_at'] = Carbon::now();
        }

        return $post;
    }
}
