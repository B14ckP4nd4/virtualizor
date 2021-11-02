<?php


namespace BlackPanda\Virtualizor;


use Illuminate\Support\ServiceProvider;

class VirtualizorServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('virtualizor',function (){
            return new Virtualizor();
        });
    }

}
