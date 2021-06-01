<?php

namespace Eypiay\Eypiay\Traits;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

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
}
