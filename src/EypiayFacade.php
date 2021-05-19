<?php

namespace Eypiay\Eypiay;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Eypiay\Eypiay\Skeleton\SkeletonClass
 */
class EypiayFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'eypiay';
    }
}
