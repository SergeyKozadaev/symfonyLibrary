<?php

namespace App\Tests\Controller;

use App\Tests\AppBasicTest;

class BookControllerTest extends AppBasicTest
{
    public function testBookAdd()
    {
        $this->checkUserRegistration();

        $this->checkUserAuthorisation();

        $this->checkBookAddFormExistence();

        $this->checkBookAdd();
    }
}
