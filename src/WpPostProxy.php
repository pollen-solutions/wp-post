<?php

declare(strict_types=1);

namespace Pollen\WpPost;

use Pollen\Support\Exception\ProxyInvalidArgumentException;
use Pollen\Support\ProxyResolver;
use RuntimeException;
use WP_Query;
use WP_Post;

/**
 * @see \Pollen\WpPost\WpPostProxyInterface
 */
trait WpPostProxy
{
    /**
     * Related post manager instance.
     * @var WpPostManagerInterface|null
     */
    private ?WpPostManagerInterface $wpPostManager = null;

    /**
     * Resolve post manager instance|Retrieve list of post instances|Get a post instance.
     *
     * @param true|string|int|WP_Post|WP_Query|array|null $query
     *
     * @return WpPostManagerInterface|WpPostQueryInterface|WpPostQueryInterface[]|array
     */
    public function wpPost($query = null)
    {
        if ($this->wpPostManager === null) {
            try {
                $this->wpPostManager = WpPostManager::getInstance();
            } catch (RuntimeException $e) {
                $this->wpPostManager = ProxyResolver::getInstance(
                    WpPostManagerInterface::class,
                    WpPostManager::class,
                    method_exists($this, 'getContainer') ? $this->getContainer() : null
                );
            }
        }

        if ($query === null) {
            return $this->wpPostManager;
        }

        if (is_array($query) || ($query instanceof WP_Query)) {
            return $this->wpPostManager->fetch($query);
        }

        if ($query === true) {
            $query = null;

            global $wp_query;

            if ($wp_query && !$wp_query->is_singular) {
                return $this->wpPostManager->fetch();
            }
        }

        if ($post = $this->wpPostManager->get($query)) {
            return $post;
        }

        throw new ProxyInvalidArgumentException('WpPostQueried is unavailable');
    }

    /**
     * Set the related post manager instance.
     *
     * @param WpPostManagerInterface $wpPostManager
     *
     * @return void
     */
    public function setWpPostManager(WpPostManagerInterface $wpPostManager): void
    {
        $this->wpPostManager = $wpPostManager;
    }
}
