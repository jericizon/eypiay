<?php

namespace JericIzon\Eypiay\Traits;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Hash;
use Log;
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

    protected function getValidations(string $tableName)
    {
        try {
            return config('eypiay.tables.' . $tableName . '.validations') ?? [];
        } catch(\Exception $error) {
            $this->_logError($error);
            return [];
        }
    }

    protected function getResource(string $tableName)
    {
        try {
            return config('eypiay.tables.' . $tableName . '.resource') ?? config('eypiay.tables.resource') ?? '\JericIzon\Eypiay\Http\Resources\EypiayBaseResource';
        } catch (\Exception $error) {
            $this->_logError($error);
            return '\JericIzon\Eypiay\Http\Resources\EypiayBaseResource';
        }
    }

    protected function castInputs(string $tableName, array $input)
    {
        try {
            $casts = config('eypiay.tables.' . $tableName . '.casts') ?? [];
            if(count($casts) > 0) {
                foreach( $casts as $column => $cast ) {
                    if(isset($input[$column])) {
                        $input[$column] = $this->_doCast($cast, $input[$column]);
                    }
                }
            }
            return $input;
        } catch (\Exception $error) {
            $this->_logError($error);
            return $input;
        }
    }

    private function _logError($error)
    {
        if(!config('eypiay.debug')) {
            return;
        }
        Log::error($error);
    }
    private function _doCast($type, $value)
    {
        if($type === 'hash') {
            $value = Hash::make($value);
        }
        return $value;
    }
}

