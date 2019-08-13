<?php

namespace Biigle\Tests\Modules\Module;

use TestCase;
use Biigle\Modules\Module\ModuleServiceProvider;

class ModuleServiceProviderTest extends TestCase {

   public function testServiceProvider()
   {
      $this->assertTrue(class_exists(ModuleServiceProvider::class));
   }
}
