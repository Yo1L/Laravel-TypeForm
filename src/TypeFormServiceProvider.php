<?php
namespace Yo1L\LaravelTypeForm;

/**
 *
 * @author Yo1L <kyleviavant@gmail.com>
 */
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;

class TypeFormServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * boot
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([__DIR__.'/config/typeform.php' => config_path('typeform.php')]);
    }
    
    /**
     * regirster
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(TypeForm::class, function () {
            return new TypeForm();
        });

        $this->app->alias(TypeForm::class, 'TypeForm');

        config(['config/typeform.php']);
    }
}
