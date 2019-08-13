<?php
namespace Biigle\Modules\Module;

use Biigle\Services\Modules;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider {

   /**
   * Bootstrap the application events.
   *
   * @param Modules $modules
   * @return  void
   */
   public function boot(Modules $modules)
   {
      $this->loadViewsFrom(__DIR__.'/resources/views', 'module');
      $modules->register('module', [
            'viewMixins' => [
                'dashboardMain',
            ],
            'controllerMixins' => [
                //
            ],
            'apidoc' => [
               //__DIR__.'/Http/Controllers/Api/',
            ],
        ]);
   }

   /**
   * Register the service provider.
   *
   * @return  void
   */
   public function register()
   {
      //
   }
}
