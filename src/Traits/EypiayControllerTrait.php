<?php

namespace JericIzon\Eypiay\Traits;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Hash;
trait EypiayControllerTrait
{
    protected function tableAvailable(string $tableName)
    {
        return (bool) config('eypiay.tables.' . $tableName) ?? false;
    }

    protected function getModel(string $tableName)
    {
        return resolve(config('eypiay.tables.' . $tableName . '.model') ?? '');
    }

    protected function castInputs(string $tableName, array $input)
    {
        $casts = config('eypiay.tables.' . $tableName . '.casts') ?? [];
        if(count($casts) > 0) {
            foreach( $casts as $column => $cast ) {
                if(isset($input[$column])) {
                    $input[$column] = $this->_doCast($cast, $input[$column]);
                }
            }
        }
        return $input;
    }

    private function _doCast($type, $value)
    {
        if($type === 'hash') {
            $value = Hash::make($value);
        }
        return $value;
    }
}

