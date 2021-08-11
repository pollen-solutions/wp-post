<?php

declare(strict_types=1);

namespace Pollen\WpPost;

use Pollen\Pagination\Adapters\WpQueryPaginatorInterface;
use Pollen\Support\Concerns\BootableTrait;
use Pollen\Support\Concerns\ConfigBagAwareTrait;
use Pollen\Support\Exception\ManagerRuntimeException;
use Pollen\Support\Proxy\ContainerProxy;
use Psr\Container\ContainerInterface as Container;
use RuntimeException;
use WP_Post_Type;

class WpPostManager implements WpPostManagerInterface
{
    use BootableTrait;
    use ConfigBagAwareTrait;
    use ContainerProxy;

    /**
     * Post manager main instance.
     * @var WpPostManagerInterface|null
     */
    private static ?WpPostManagerInterface $instance = null;

    /**
     * Post type manager related instance.
     * @var WpPostTypeManagerInterface|null
     */
    protected ?WpPostTypeManagerInterface $postTypeManager = null;

    /**
     * @param array $config
     * @param Container|null $container
     *
     * @return void
     */
    public function __construct(array $config = [], ?Container $container = null)
    {
        if (!self::$instance instanceof static) {
            self::$instance = $this;
        } else {
            return;
        }

        $this->setConfig($config);

        if ($container !== null) {
            $this->setContainer($container);
        }

        $this->boot();
    }

    /**
     * Retrieve post manager main instance.
     *
     * @return static
     */
    public static function getInstance(): WpPostManagerInterface
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }
        throw new ManagerRuntimeException(sprintf('Unavailable [%s] instance', __CLASS__));
    }

    /**
     * @inheritDoc
     */
    public function boot(): WpPostManagerInterface
    {
        if (!$this->isBooted()) {
            if (!function_exists('add_action')) {
                throw new RuntimeException('add_action function is missing.');
            }

            add_action(
                'init',
                function () {
                    global $wp_post_types;

                    foreach ($this->postTypeManager()->all() as $name => $postType) {
                        $postType->boot();

                        if (!isset($wp_post_types[$name])) {
                            register_post_type($name, $postType->params()->all());
                        }

                        if ($wp_post_types[$name] instanceof WP_Post_Type) {
                            $postType->setWpPostType($wp_post_types[$name]);
                        }

                        if ($taxonomies = $postType->params('taxonomies', [])) {
                            foreach ($taxonomies as $taxonomy) {
                                register_taxonomy_for_object_type($taxonomy, $postType->getName());
                            }
                        }
                    }
                },
                11
            );

            add_action(
                'init',
                function () {
                    global $wp_post_types;

                    foreach ($wp_post_types as $name => $attrs) {
                        if (!$this->getType($name)
                            && ($postType = $this->registerType($name, get_object_vars($attrs)))
                        ) {
                            $postType->boot();
                        }
                    }
                },
                999999
            );

            $this->setBooted();
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function fetch($query = null): array
    {
        return WpPostQuery::fetch($query);
    }

    /**
     * @inheritDoc
     */
    public function get($post = null): ?WpPostQueryInterface
    {
        return WpPostQuery::create($post);
    }

    /**
     * @inheritDoc
     */
    public function getType(string $name): ?WpPostTypeInterface
    {
        return $this->postTypeManager()->get($name);
    }

    /**
     * @inheritDoc
     */
    public function paginator(): ?WpQueryPaginatorInterface
    {
        return WpPostQuery::paginator();
    }

    /**
     * @inheritDoc
     */
    public function postTypeManager(): WpPostTypeManagerInterface
    {
        if ($this->postTypeManager === null) {
            $this->postTypeManager = $this->containerHas(WpPostTypeManagerInterface::class)
                ? $this->containerGet(WpPostTypeManagerInterface::class) : new WpPostTypeManager($this);
        }
        return $this->postTypeManager;
    }

    /**
     * @inheritDoc
     */
    public function registerType(string $name, $postTypeDef = []): WpPostTypeInterface
    {
        return $this->postTypeManager()->register($name, $postTypeDef);
    }
}