<?php

declare(strict_types=1);

namespace Pollen\WpPost;

use Pollen\Support\Proxy\ContainerProxy;
use Psr\Container\ContainerInterface as Container;

class WpPostTypeManager implements WpPostTypeManagerInterface
{
    use ContainerProxy;
    use WpPostProxy;

    /**
     * List of registered post type instances.
     * @var WpPostTypeInterface[]|array
     */
    public array $postTypes = [];

    /**
     * @param WpPostManagerInterface $wpPostManager
     * @param Container|null $container
     */
    public function __construct(WpPostManagerInterface $wpPostManager, ?Container $container = null)
    {
        $this->setWpPostManager($wpPostManager);

        if ($container !== null) {
            $this->setContainer($container);
        }
    }

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        return $this->postTypes;
    }

    /**
     * @inheritDoc
     */
    public function get(string $name): ?WpPostTypeInterface
    {
        return $this->postTypes[$name] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function register(string $name, $postTypeDef): WpPostTypeInterface
    {
        if (!$postTypeDef instanceof WpPostTypeInterface) {
            $postType = new WpPostType($name, is_array($postTypeDef) ? $postTypeDef : []);
        } else {
            $postType = $postTypeDef;
        }
        $this->postTypes[$name] = $postType;

        return $postType;
    }
}