<?php

namespace TekPart\License\Facades;

use Illuminate\Support\Facades\Facade;

class License extends Facade
{
    /**
     * الحصول على اسم المكون المسجل.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'teklicense';
    }
}
