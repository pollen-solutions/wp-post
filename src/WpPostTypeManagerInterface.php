<?php

declare(strict_types=1);

namespace Pollen\WpPost;

use Pollen\Support\Proxy\ContainerProxyInterface;

interface WpPostTypeManagerInterface extends ContainerProxyInterface, WpPostProxyInterface
{
    /**
     * Return the list of registered post type instances.
     *
     * @return WpPostTypeInterface[]|array
     */
    public function all(): array;

    /**
     * Get post type instance by identifier name.
     *
     * @param string $name
     *
     * @return WpPostTypeInterface|null
     */
    public function get(string $name): ?WpPostTypeInterface;

    /**
     * Register post type.
     *
     * @param string $name
     * @param WpPostTypeInterface|array $postTypeDef
     *
     * @return WpPostTypeInterface
     */
    public function register(string $name, $postTypeDef): WpPostTypeInterface;
}