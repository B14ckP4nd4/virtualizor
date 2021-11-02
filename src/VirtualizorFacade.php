<?php


namespace BlackPanda\Virtualizor;


use Illuminate\Support\Facades\Facade;

class VirtualizorFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'virtualizor';
    }

}
