<?php

namespace JericIzon\Eypiay\Traits;

trait EypiayControllerTrait
{
    public function tableAvailable(string $tableName)
    {
        return (bool) config('eypiay.tables.' . $tableName) ?? false;
    }

    public function getModel(string $tableName)
    {
        return resolve(config('eypiay.tables.' . $tableName . '.model') ?? '');    }
}

