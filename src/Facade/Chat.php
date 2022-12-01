<?php

namespace DevsFort\Pigeon\Chat\Facade;

use Illuminate\Support\Facades\Facade;

class Chat extends Facade
{

    protected static function getFacadeAccessor()
    {
        return "DevsFortChat";
    }
}