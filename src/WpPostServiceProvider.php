<?php

declare(strict_types=1);

namespace Pollen\WpPost;

use Pollen\Container\BootableServiceProvider;

class WpPostServiceProvider extends BootableServiceProvider
{
    /**
     * @var string[]
     */
    protected $provides = [
        WpPostManagerInterface::class,
        WpPostTypeManagerInterface::class
    ];

    /**
     * @inheritdoc
     */
    public function register(): void
    {
        $this->getContainer()->share(WpPostManagerInterface::class, function() {
            return new WpPostManager([], $this->getContainer());
        });

        $this->getContainer()->share(
            WpPostTypeManagerInterface::class,
            function () {
                return new WpPostTypeManager(
                    $this->getContainer()->get(WpPostManagerInterface::class), $this->getContainer()
                );
            }
        );
    }
}