<?php

namespace Biigle\Tests\Modules\Module\Http\Controllers;

use TestCase;
use Biigle\Tests\UserTest;

class QuotesControllerTest extends TestCase {

   public function testRoute()
   {
      $user = UserTest::create();

      // Redirect to login page.
      $this->get('quotes')->assertStatus(302);

      $this->be($user);
      $this->get('quotes')->assertStatus(200);
   }
}
