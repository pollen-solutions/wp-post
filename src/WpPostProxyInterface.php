<?php

declare(strict_types=1);

namespace Pollen\WpPost;

use WP_Post;
use Wp_Query;

interface WpPostProxyInterface
{
    /**
     * Resolve post manager instance|Retrieve list of post instances|Get a post instance.
     *
     * @param true|string|int|WP_Post|WP_Query|array|null $query
     *
     * @return WpPostManagerInterface|WpPostQueryInterface|WpPostQueryInterface[]|array
     */
    public function wpPost($query = null);

    /**
     * Set the related post manager instance.
     *
     * @param WpPostManagerInterface $wpPostManager
     *
     * @return void
     */
    public function setWpPostManager(WpPostManagerInterface $wpPostManager): void;
}
