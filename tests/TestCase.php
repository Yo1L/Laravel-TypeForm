<?php
namespace Yo1L\LaravelTypeForm\Test;

use Yo1L\LaravelTypeForm\TypeFormFacade;
use Yo1L\LaravelTypeForm\TypeFormServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    /**
     * Load package service provider
     * @param  \Illuminate\Foundation\Application $app
     * @return Yo1L\TypeForm\TypeFormServiceProvider
     */
    protected function getPackageProviders($app)
    {
        return [TypeFormServiceProvider::class];
    }
    /**
     * Load package alias
     * @param  \Illuminate\Foundation\Application $app
     * @return array
     */
    /*protected function getPackageAliases($app)
    {
        return [
            'TypeForm' => TypeFormFacade::class,
        ];
    }*/
}
