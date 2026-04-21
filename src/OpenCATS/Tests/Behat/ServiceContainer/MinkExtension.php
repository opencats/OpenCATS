<?php

namespace OpenCATS\Tests\Behat\ServiceContainer;

use Behat\MinkExtension\ServiceContainer\MinkExtension as BaseMinkExtension;
use OpenCATS\Tests\Behat\ServiceContainer\Driver\BrowserKitFactory;

class MinkExtension extends BaseMinkExtension
{
    public function __construct()
    {
        parent::__construct();

        $this->registerDriverFactory(new BrowserKitFactory());
    }
}
