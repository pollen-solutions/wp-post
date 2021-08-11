<?php

declare(strict_types=1);

namespace Pollen\WpPost;

use Pollen\Pagination\Adapters\WpQueryPaginatorInterface;
use Pollen\Support\Concerns\BootableTraitInterface;
use Pollen\Support\Concerns\ConfigBagAwareTraitInterface;
use Pollen\Support\Proxy\ContainerProxyInterface;
use WP_Query;
use WP_Post;

interface WpPostManagerInterface extends BootableTraitInterface, ConfigBagAwareTraitInterface, ContainerProxyInterface
{
    /**
     * Booting.
     *
     * @return static
     */
    public function boot(): WpPostManagerInterface;

    /**
     * List of post instance from a WP_Query|from a list of query arguments|for the current queried WordPress posts.
     *
     * @param WP_Query|array|null $query
     *
     * @return WpPostQueryInterface[]|array
     */
    public function fetch($query = null): array;

    /**
     * Get a post instance from post ID|from post name|from WP_Post object|for current queried WordPress post.
     *
     * @param string|int|WP_Post|null $post
     *
     * @return WpPostQueryInterface|null
     */
    public function get($post = null): ?WpPostQueryInterface;

    /**
     * Get post type instance from its identifier name.
     *
     * @param string $name
     *
     * @return WpPostTypeInterface|null
     */
    public function getType(string $name): ?WpPostTypeInterface;

    /**
     * Retrieve paginator instance from the last query request..
     *
     * @return WpQueryPaginatorInterface|null
     */
    public function paginator(): ?WpQueryPaginatorInterface;

    /**
     * Retrieve related post type manager instance.
     *
     * @return WpPostTypeManagerInterface
     */
    public function postTypeManager(): WpPostTypeManagerInterface;

    /**
     * Register post type.
     *
     * @param string $name
     * @param array|WpPostTypeInterface $postTypeDef
     *
     * @return WpPostTypeInterface|null
     */
    public function registerType(string $name, $postTypeDef = []): ?WpPostTypeInterface;
}