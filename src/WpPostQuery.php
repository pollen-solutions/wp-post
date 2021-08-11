<?php

declare(strict_types=1);

namespace Pollen\WpPost;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Pollen\Pagination\Adapters\WpQueryPaginator;
use Pollen\Pagination\Adapters\WpQueryPaginatorInterface;
use Pollen\Support\DateTime;
use Pollen\Support\ParamsBag;
use Pollen\Support\Str;
use Pollen\WpComment\WpCommentQuery;
use Pollen\WpTerm\WpTermQuery;
use Pollen\WpUser\WpUserQuery;
use Pollen\WpUser\WpUserQueryInterface;
use WP_Post;
use WP_Query;
use WP_Term_Query;

/**
 * @property-read int $ID
 * @property-read int $post_author
 * @property-read string $post_date
 * @property-read string $post_date_gmt
 * @property-read string $post_content
 * @property-read string $post_title
 * @property-read string $post_excerpt
 * @property-read string $post_status
 * @property-read string $comment_status
 * @property-read string $ping_status
 * @property-read string $post_password
 * @property-read string $post_name
 * @property-read string $to_ping
 * @property-read string $pinged
 * @property-read string $post_modified
 * @property-read string $post_modified_gmt
 * @property-read string $post_content_filtered
 * @property-read int $post_parent
 * @property-read string $guid
 * @property-read int $menu_order
 * @property-read string $post_type
 * @property-read string $post_mime_type
 * @property-read int $comment_count
 * @property-read string $filter
 */
class WpPostQuery extends ParamsBag implements WpPostQueryInterface
{
    use WpPostProxy;

    /**
     * List of built-in classe names by post type.
     * @var array<string, string>
     */
    protected static array $builtInClasses = [];

    /**
     * List of defaults query request arguments.
     * @var array
     */
    protected static array $defaultArgs = [];

    /**
     * Fallback class name.
     * @var string|null
     */
    protected static ?string $fallbackClass = null;

    /**
     * Paginator instance from the last fetch request.
     * @var WpQueryPaginatorInterface|null
     */
    protected static ?WpQueryPaginatorInterface $paginator = null;

    /**
     * Related post type identifier name|List of related post type identifier names.
     * @var string|string[]|null
     */
    protected static $postType = 'any';

    /**
     * Related parent instance.
     * @var WpPostQueryInterface|false|null
     */
    protected $parent;

    /**
     * Related WP_Post instance.
     * @var WP_Post|null
     */
    protected ?WP_Post $wpPost = null;

    /**
     * @param WP_Post|null $wp_post
     *
     * @return void
     */
    public function __construct(?WP_Post $wp_post = null)
    {
        if ($this->wpPost = $wp_post instanceof WP_Post ? $wp_post : null) {
            parent::__construct($this->wpPost->to_array());
        }
    }

    /**
     * @inheritDoc
     */
    public static function build(object $wp_post): ?WpPostQueryInterface
    {
        if (!$wp_post instanceof WP_Post) {
            return null;
        }

        $classes = self::$builtInClasses;
        $post_type = $wp_post->post_type;

        $class = $classes[$post_type] ?? (self::$fallbackClass ?: static::class);

        return class_exists($class) ? new $class($wp_post) : new static($wp_post);
    }

    /**
     * @inheritDoc
     */
    public static function create($postDef = null, ...$args): ?WpPostQueryInterface
    {
        if (is_numeric($postDef)) {
            return static::createFromId((int)$postDef);
        }
        if (is_string($postDef)) {
            return static::createFromName($postDef);
        }
        if ($postDef instanceof WP_Post) {
            return static::build($postDef);
        }
        if ($postDef instanceof WpPostQueryInterface) {
            return static::createFromId($postDef->getId());
        }
        if (is_null($postDef)) {
            return static::createFromGlobal();
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public static function createFromEloquent(EloquentModel $model): ?WpPostQueryInterface
    {
        return static::createFromId((new WP_Post((object)$model->getAttributes()))->ID ?: 0);
    }

    /**
     * @inheritDoc
     */
    public static function createFromGlobal(): ?WpPostQueryInterface
    {
        global $post;

        return ($post instanceof WP_Post) ? static::createFromId($post->ID ?? 0) : null;
    }

    /**
     * @inheritDoc
     */
    public static function createFromId(int $post_id): ?WpPostQueryInterface
    {
        if ($post_id && ($wp_post = get_post($post_id)) && ($wp_post instanceof WP_Post)) {
            if (!$instance = static::build($wp_post)) {
                return null;
            }
            return $instance::is($instance) ? $instance : null;
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public static function createFromName(string $post_name): ?WpPostQueryInterface
    {
        $wpQuery = new WP_Query(static::parseQueryArgs(['name' => $post_name]));

        return (1 === (int)$wpQuery->found_posts) ? static::createFromId(current($wpQuery->posts)->ID ?? 0) : null;
    }

    /**
     * @inheritDoc
     */
    public static function createFromPostdata(array $postdata): ?WpPostQueryInterface
    {
        return ($instance = static::createFromId((new WP_Post((object)$postdata))->ID ?? 0)) ? $instance : null;
    }

    /**
     * @inheritDoc
     */
    public static function fetch($query = null): array
    {
        if (is_array($query)) {
            return static::fetchFromArgs($query);
        }
        if ($query instanceof WP_Query) {
            return static::fetchFromWpQuery($query);
        }
        if (is_null($query)) {
            return static::fetchFromGlobal();
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function fetchFromArgs(array $args = []): array
    {
        return static::fetchFromWpQuery(new WP_Query(static::parseQueryArgs($args)));
    }

    /**
     * @inheritDoc
     */
    public static function fetchFromEloquent(EloquentCollection $collection): array
    {
        $instances = [];
        foreach ($collection->toArray() as $item) {
            if ($instance = static::createFromId((new WP_Post((object)$item))->ID ?: 0)) {
                $instances[] = $instance;
            }
        }

        return $instances;
    }

    /**
     * @inheritDoc
     */
    public static function fetchFromGlobal(): array
    {
        global $wp_query;

        return static::fetchFromWpQuery($wp_query);
    }

    /**
     * @inheritDoc
     */
    public static function fetchFromIds(array $ids): array
    {
        if (!empty($ids)) {
            $args = static::parseQueryArgs(['post__in' => $ids, 'posts_per_page' => count($ids)]);

            return static::fetchFromWpQuery(new WP_Query($args));
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function fetchFromWpQuery(WP_Query $wp_query): array
    {
        $wp_posts = $wp_query->posts ?? [];
        $results = [];
        foreach ($wp_posts as $wp_post) {
            if (!$instance = static::createFromId($wp_post->ID)) {
                continue;
            }

            if (($postType = static::$postType) && ($postType !== 'any')) {
                if ($instance->typeIn($postType)) {
                    $results[] = $instance;
                }
            } else {
                $results[] = $instance;
            }
        }

        if (class_exists(WpQueryPaginator::class)) {
            static::$paginator = new WpQueryPaginator($wp_query);
        }

        return $results;
    }

    /**
     * @inheritDoc
     */
    public static function is($instance): bool
    {
        return $instance instanceof static &&
            (!(($postType = static::$postType) && ($postType !== 'any')) || $instance->typeIn((array)$postType));
    }

    /**
     * @inheritDoc
     */
    public static function paginator(): WpQueryPaginatorInterface
    {
        if (static::$paginator === null && class_exists(WpQueryPaginator::class)) {
            static::$paginator = new WpQueryPaginator();
        }

        return static::$paginator;
    }

    /**
     * @inheritDoc
     */
    public static function parseQueryArgs(array $args = []): array
    {
        if (!isset($args['post_type'])) {
            $args['post_type'] = static::$postType ?: 'any';
        }

        return array_merge(static::$defaultArgs, $args);
    }

    /**
     * @inheritDoc
     */
    public static function setBuiltInClass(string $post_type, string $classname): void
    {
        if ($post_type === 'any') {
            self::setFallbackClass($classname);
        } else {
            self::$builtInClasses[$post_type] = $classname;
        }
    }

    /**
     * @inheritDoc
     */
    public static function setDefaultArgs(array $args): void
    {
        self::$defaultArgs = $args;
    }

    /**
     * @inheritDoc
     */
    public static function setFallbackClass(string $classname): void
    {
        self::$fallbackClass = $classname;
    }

    /**
     * @inheritDoc
     */
    public static function setPostType($post_type): void
    {
        static::$postType = $post_type;
    }

    /**
     * @inheritDoc
     */
    public function getArchiveUrl(): ?string
    {
        return get_post_type_archive_link($this->getType()) ?: null;
    }

    /**
     * @inheritDoc
     */
    public function getAuthorId(): int
    {
        return (int)$this->get('post_author', 0);
    }

    /**
     * @inheritDoc
     */
    public function getBeforeMore(): ?string
    {
        return $this->hasMore() && ($parts = preg_split('/<!--more(.*?)?-->/', $this->getContent(true)))
            ? $parts[0] : null;
    }

    /**
     * @inheritDoc
     */
    public function getChildren(?int $per_page = -1, int $page = 1, array $args = []): array
    {
        if (is_null($per_page)) {
            $per_page = get_option('posts_per_page');
        }

        return static::fetchFromArgs(
            array_merge(
                $args,
                [
                    'paged'          => $page,
                    'post_parent'    => $this->getId(),
                    'post_status'    => 'publish',
                    'posts_per_page' => $per_page,
                ]
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function getClass(array $classes = [], bool $html = false): string
    {
        $_classes = implode(' ', $this->getClasses($classes));

        return $html ? 'class="' . $_classes . '"' : $_classes;
    }

    /**
     * @inheritDoc
     */
    public function getClasses(array $classes = []): array
    {
        return get_post_class($classes, $this->getId());
    }

    /**
     * @inheritDoc
     */
    public function getContent(bool $raw = false): string
    {
        $content = (string)$this->get('post_content');

        if (!$raw) {
            $content = apply_filters('the_content', $content);
            $content = str_replace(']]>', ']]&gt;', $content);
        }

        return $content;
    }

    /**
     * @inheritDoc
     */
    public function getDate(bool $gmt = false, string $format = null): string
    {
        return $this->getDateTime($gmt)->formatLocale($format ?? get_option('date_format'));
    }

    /**
     * @inheritDoc
     */
    public function getDateTime(): DateTime
    {
        return Datetime::createFromTimeString($this->get('post_date'));
    }

    /**
     * @inheritDoc
     */
    public function getEditUrl(): string
    {
        return get_edit_post_link($this->getId());
    }

    /**
     * @inheritDoc
     */
    public function getExcerpt(bool $raw = false): string
    {
        if (!$excerpt = (string)$this->get('post_excerpt')) {
            $text = $this->get('post_content');

            // @see /wp-includes/post-template.php \get_the_excerpt()
            $text = strip_shortcodes($text);
            $text = apply_filters('the_content', $text);
            $text = str_replace(']]>', ']]&gt;', $text);

            $excerpt_length = apply_filters('excerpt_length', 55);
            $excerpt_more = apply_filters('excerpt_more', ' ' . '[&hellip;]');
            $excerpt = wp_trim_words($text, $excerpt_length, $excerpt_more);
        }

        if ($raw) {
            return $excerpt;
        }
        return $excerpt ? (string)apply_filters('get_the_excerpt', $excerpt) : '';
    }

    /**
     * @inheritDoc
     */
    public function getGuid(): string
    {
        return (string)$this->get('guid');
    }

    /**
     * @inheritDoc
     */
    public function getId(): int
    {
        return (int)$this->get('ID', 0);
    }

    /**
     * @inheritDoc
     */
    public function getMeta(string $meta_key, bool $isSingle = false, $default = null)
    {
        return get_post_meta($this->getId(), $meta_key, $isSingle) ?: $default;
    }

    /**
     * @inheritDoc
     */
    public function getMetaKeys(): array
    {
        return get_post_custom_keys($this->getId()) ?: [];
    }

    /**
     * @inheritDoc
     */
    public function getMetaMulti(string $meta_key, $default = null)
    {
        return $this->getMeta($meta_key, false, $default);
    }

    /**
     * @inheritDoc
     */
    public function getMetaSingle(string $meta_key, $default = null)
    {
        return $this->getMeta($meta_key, true, $default);
    }

    /**
     * @inheritDoc
     */
    public function getModified(bool $gmt = false, string $format = null): string
    {
        return $this->getModifiedDateTime($gmt)->formatLocale($format ?? get_option('date_format'));
    }

    /**
     * @inheritDoc
     */
    public function getModifiedDateTime(): DateTime
    {
        return Datetime::createFromTimeString($this->get('post_modified'));
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->getSlug();
    }

    /**
     * @inheritDoc
     */
    public function getParent(): ?WpPostQueryInterface
    {
        if (is_null($this->parent) && ($parent_id = $this->getParentId())) {
            $this->parent = static::createFromId($parent_id) ?: false;
        } else {
            $this->parent = false;
        }

        return $this->parent ?: null;
    }

    /**
     * @inheritDoc
     */
    public function getParentId(): int
    {
        return (int)$this->get('post_parent', 0);
    }

    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
        return rtrim(str_replace(home_url('/'), '', $this->getPermalink()), '/');
    }

    /**
     * @inheritDoc
     */
    public function getPermalink(): string
    {
        return get_permalink($this->getId());
    }

    /**
     * @inheritDoc
     */
    public function getQueriedAuthor(): ?WpUserQueryInterface
    {
        return WpUserQuery::createFromId($this->getAuthorId());
    }

    /**
     * @inheritDoc
     */
    public function getQueriedComments(array $args = []): array
    {
        return WpCommentQuery::fetchFromArgs(
            array_merge(
                $args,
                [
                    'post_id' => $this->getId(),
                ]
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function getQueriedThumbnail(): ?WpPostQueryInterface
    {
        return self::createFromId($this->getThumbnailId());
    }

    /**
     * @inheritDoc
     */
    public function getQueriedTerms($taxonomy, array $args = []): array
    {
        return WpTermQuery::fetchFromArgs(
            array_merge(
                $args,
                [
                    'taxonomy'   => $taxonomy,
                    'object_ids' => $this->getId(),
                ]
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function getShortLink(): string
    {
        return wp_get_shortlink($this->getId());
    }

    /**
     * @inheritDoc
     */
    public function getSlug(): string
    {
        return (string)$this->get('post_name');
    }

    /**
     * @inheritDoc
     */
    public function getTeaser(
        int $length = 255,
        string $teaser = ' [&hellip;]',
        bool $use_tag = true,
        bool $uncut = true
    ): string {
        return Str::teaser($this->getContent(), $length, $teaser, $use_tag, $uncut);
    }

    /**
     * @inheritDoc
     */
    public function getTerms($taxonomy, array $args = []): array
    {
        $args['taxonomy'] = $taxonomy;
        $args['object_ids'] = $this->getId();

        return (new WP_Term_Query($args))->terms ?: [];
    }

    /**
     * @inheritDoc
     */
    public function getThumbnailId(): int
    {
        return get_post_thumbnail_id($this->getId()) ?: 0;
    }

    /**
     * @inheritDoc
     */
    public function getThumbnail($size = 'post-thumbnail', array $attrs = []): string
    {
        return get_the_post_thumbnail($this->getId(), $size, $attrs);
    }

    /**
     * @inheritDoc
     */
    public function getThumbnailSrc($size = 'post-thumbnail'): string
    {
        return get_the_post_thumbnail_url($this->getId(), $size) ?: '';
    }

    /**
     * @inheritDoc
     */
    public function getTitle(bool $raw = false): string
    {
        $title = (string)$this->get('post_title');

        return $raw ? $title : (string)apply_filters('the_title', $title, $this->getId());
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return $this->get('post_type');
    }

    /**
     * @inheritDoc
     */
    public function getWpPost(): ?WP_Post
    {
        return $this->wpPost;
    }

    /**
     * @inheritDoc
     */
    public function getWpPostType(): WpPostTypeInterface
    {
        return $this->wpPost()->getType($this->getType());
    }

    /**
     * @inheritDoc
     */
    public function hasMore(): bool
    {
        return (bool)preg_match('/<!--more(.*?)?-->/', $this->getContent(true));
    }

    /**
     * @inheritDoc
     */
    public function hasTerm($term, string $taxonomy): bool
    {
        return has_term($term, $taxonomy, $this->getWpPost());
    }

    /**
     * @inheritDoc
     */
    public function isHierarchical(): bool
    {
        return is_post_type_hierarchical($this->getType());
    }

    /**
     * @inheritDoc
     */
    public function isType(string $post_type): bool
    {
        return $this->getType() === $post_type;
    }

    /**
     * @inheritDoc
     */
    public function typeIn(array $post_types): bool
    {
        return in_array($this->getType(), $post_types, true);
    }
}
