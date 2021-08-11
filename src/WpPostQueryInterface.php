<?php

declare(strict_types=1);

namespace Pollen\WpPost;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Pollen\Pagination\Adapters\WpQueryPaginatorInterface;
use Pollen\Support\DateTime;
use Pollen\Support\ParamsBagInterface;
use Pollen\WpComment\WpCommentQueryInterface;
use Pollen\WpTerm\WpTermQueryInterface;
use Pollen\WpUser\WpUserQueryInterface;
use WP_Post;
use WP_Query;
use WP_Term;

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
interface WpPostQueryInterface extends ParamsBagInterface
{
    /**
     * Build an instance from WP_Post object.
     *
     * @param WP_Post|object $wp_post
     *
     * @return static
     */
    public static function build(object $wp_post): ?WpPostQueryInterface;

    /**
     * Create an instance from a post ID|from a post name|from a WP_Post object|for current queried WordPress post.
     *
     * @param int|string|WP_Post|null $postDef
     * @param mixed ...$args List of custom arguments
     *
     * @return static|null
     */
    public static function create($postDef = null, ...$args): ?WpPostQueryInterface;

    /**
     * Create an instance from an Eloquent Model instance.
     *
     * @param EloquentModel $model
     *
     * @return static|null
     */
    public static function createFromEloquent(EloquentModel $model): ?WpPostQueryInterface;

    /**
     * Create an instance for the current post.
     *
     * @return static|null
     */
    public static function createFromGlobal(): ?WpPostQueryInterface;

    /**
     * Create an instance from post ID.
     *
     * @param int $post_id
     *
     * @return static|null
     */
    public static function createFromId(int $post_id): ?WpPostQueryInterface;

    /**
     * Create an instance from post name.
     *
     * @param string $post_name
     *
     * @return static|null
     */
    public static function createFromName(string $post_name): ?WpPostQueryInterface;

    /**
     * Create an instance from post data array.
     *
     * @param array $postdata ID required.
     *
     * @return static|null
     */
    public static function createFromPostdata(array $postdata): ?WpPostQueryInterface;

    /**
     * Retrieve list of instances from WP_Query object|from a list of query arguments.
     *
     * @param WP_Query|array|null $query
     *
     * @return WpPostQueryInterface[]|array
     */
    public static function fetch($query = null): array;

    /**
     * Retrieve list of instances from a list of query arguments.
     * @see https://developer.wordpress.org/reference/classes/wp_query/
     *
     * @param array $args Liste des arguments de la requête récupération des éléments.
     *
     * @return array
     */
    public static function fetchFromArgs(array $args = []): array;

    /**
     * Retrieve list of instances from an Eloquent Collection instance.
     *
     * @param EloquentCollection $collection
     *
     * @return array
     */
    public static function fetchFromEloquent(EloquentCollection $collection): array;

    /**
     * Retrieve list of instances from WordPress global request.
     * @see https://developer.wordpress.org/reference/classes/wp_query/
     *
     * @return array
     */
    public static function fetchFromGlobal(): array;

    /**
     * Retrieve list of instances from a list of post IDs.
     * @see https://developer.wordpress.org/reference/classes/wp_query/
     *
     * @param int[] $ids
     *
     * @return array
     */
    public static function fetchFromIds(array $ids): array;

    /**
     * Retrieve list of instances from WP_Query object.
     * @see https://developer.wordpress.org/reference/classes/wp_query/
     *
     * @param WP_Query $wp_query
     *
     * @return array
     */
    public static function fetchFromWpQuery(WP_Query $wp_query): array;

    /**
     * Check class instance integrity.
     *
     * @param WpPostQueryInterface|object $instance
     *
     * @return bool
     */
    public static function is($instance): bool;

    /**
     * Retrieve paginator instance from the last fetch request.
     *
     * @return WpQueryPaginatorInterface
     */
    public static function paginator(): WpQueryPaginatorInterface;

    /**
     * Parse term query arguments.
     *
     * @param array $args
     *
     * @return array
     */
    public static function parseQueryArgs(array $args = []): array;

    /**
     * Set a built-in class name by post type.
     *
     * @param string $post_type
     * @param string $classname
     *
     * @return void
     */
    public static function setBuiltInClass(string $post_type, string $classname): void;

    /**
     * Set the defaults list of post query arguments.
     *
     * @param array $args
     *
     * @return void
     */
    public static function setDefaultArgs(array $args): void;

    /**
     * Set the fallback class name.
     *
     * @param string $classname Nom de qualification de la classe.
     *
     * @return void
     */
    public static function setFallbackClass(string $classname): void;

    /**
     * Set related post type|List of related post type by its identifier name.
     *
     * @param string|array $post_type
     *
     * @return void
     */
    public static function setPostType($post_type): void;

    /**
     * Gets archive page url.
     *
     * @return string|null
     */
    public function getArchiveUrl(): ?string;

    /**
     * Gets author ID.
     *
     * @return int
     */
    public function getAuthorId(): int;

    /**
     * Gets content before more tag <!--more-->.
     *
     * @return string|null
     */
    public function getBeforeMore(): ?string;

    /**
     * Gets list of children instances
     *
     * @param int|null $per_page
     * @param int $page
     * @param array $args
     *
     * @return WpPostQueryInterface[]
     */
    public function getChildren(?int $per_page = -1, int $page = 1, array $args = []): array;

    /**
     * Gets class list string.
     *
     * @param string[] $classes
     * @param bool $html if true add class HTML attribute key. ex. class="post"
     *
     * @return string
     */
    public function getClass(array $classes = [], bool $html = false): string;

    /**
     * Gets class list.
     *
     * @param string[] $classes Appended classes
     *
     * @return array
     */
    public function getClasses(array $classes = []): array;

    /**
     * Gets the content.
     *
     * @param bool $raw Enable/disable formatting.
     *
     * @return string
     */
    public function getContent(bool $raw = false): string;

    /**
     * Gets creation datetime string.
     *
     * @param bool $gmt .
     *
     * @return string
     */
    public function getDate(bool $gmt = false): string;

    /**
     * Gets creation datetime object
     *
     * @return DateTime
     */
    public function getDateTime(): DateTime;

    /**
     * Gets post edition url.
     *
     * @return string
     */
    public function getEditUrl(): string;

    /**
     * Gets the excerpt.
     *
     * @param bool $raw Enable/disable formatting.
     *
     * @return string
     */
    public function getExcerpt(bool $raw = false): string;

    /**
     * Gets uniq identifier.
     * {@internal Could not use in permalink.}
     * @see https://developer.wordpress.org/reference/functions/the_guid/
     *
     * @return string
     */
    public function getGuid(): string;

    /**
     * Gets ID.
     *
     * @return int
     */
    public function getId(): int;

    /**
     * Gets post meta.
     *
     * @param string $meta_key
     * @param bool $isSingle
     * @param mixed $default
     *
     * @return mixed
     */
    public function getMeta(string $meta_key, bool $isSingle = false, $default = null);

    /**
     * Gets all post meta keys.
     *
     * @return array
     */
    public function getMetaKeys(): array;

    /**
     * Gets post meta multi.
     *
     * @param string $meta_key
     * @param mixed $default
     *
     * @return string|array|mixed
     */
    public function getMetaMulti(string $meta_key, $default = null);

    /**
     * Gets post meta single.
     *
     * @param string $meta_key
     * @param mixed $default
     *
     * @return string|array|mixed
     */
    public function getMetaSingle(string $meta_key, $default = null);

    /**
     * Gets modification datetime string.
     *
     * @param bool $gmt
     *
     * @return string
     */
    public function getModified(bool $gmt = false): string;

    /**
     * Gets modification datetime object.
     *
     * @return DateTime
     */
    public function getModifiedDateTime(): DateTime;

    /**
     * Gets identifier name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Gets related parent instance.
     *
     * @return WpPostQueryInterface|null
     */
    public function getParent(): ?WpPostQueryInterface;

    /**
     * Gets related parent ID.
     *
     * @return int
     */
    public function getParentId(): int;

    /**
     * Get the permalink relative path.
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Get the permalink.
     *
     * @return string
     */
    public function getPermalink(): string;

    /**
     * Gets related author instance.
     *
     * @return WpUserQueryInterface|null
     */
    public function getQueriedAuthor(): ?WpUserQueryInterface;

    /**
     * Gets related list of comment instances.
     *
     * @param array $args
     *
     * @return WpCommentQueryInterface[]|array
     */
    public function getQueriedComments(array $args = []): array;

    /**
     * Gets related thumbnail instance.
     *
     * @return WpPostQueryInterface|null
     */
    public function getQueriedThumbnail(): ?WpPostQueryInterface;

    /**
     * Gets related list of taxonomy term instances.
     *
     * @param string|array $taxonomy
     * @param array $args
     *
     * @return WpTermQueryInterface[]|array
     */
    public function getQueriedTerms($taxonomy, array $args = []): array;

    /**
     * Gets short link.
     *
     * @return string
     */
    public function getShortLink(): string;

    /**
     * Get slug.
     *
     * @return string
     */
    public function getSlug(): string;

    /**
     * Gets a content excerpt.
     *
     * @param int $length
     * @param string $teaser default: [...].
     * @param bool $use_tag Check if more tag exists  <!--more-->.
     * @param bool $uncut Prevent word cutting.
     *
     * @return string
     */
    public function getTeaser(
        int $length = 255,
        string $teaser = ' [&hellip;]',
        bool $use_tag = true,
        bool $uncut = true
    ): string;

    /**
     * Get the related list of WP_Term.
     *
     * @param string|array $taxonomy
     * @param array $args
     *
     * @return WP_Term[]|array
     */
    public function getTerms($taxonomy, array $args = []): array;

    /**
     * Gets the thumbnail ID.
     *
     * @return int
     */
    public function getThumbnailId(): int;

    /**
     * Gets the thumbnail HTML string.
     *
     * @param string|array $size
     * @param array $attrs
     *
     * @return string
     */
    public function getThumbnail($size = 'post-thumbnail', array $attrs = []): string;

    /**
     * Gets the thumbnail src.
     *
     * @param string|array $size
     *
     * @return string
     */
    public function getThumbnailSrc($size = 'post-thumbnail'): string;

    /**
     * Gets the title.
     *
     * @param bool $raw Enable/disable formatting.
     *
     * @return string
     */
    public function getTitle(bool $raw = false): string;

    /**
     * Gets the post type identifier name.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Gets the related WP_Post object.
     *
     * @return WP_Post|null
     */
    public function getWpPost(): ?WP_Post;

    /**
     * Get the post type instance.
     *
     * @return WpPostTypeInterface
     */
    public function getWpPostType(): WpPostTypeInterface;

    /**
     * Check if more tag exist in content.
     *
     * @return bool
     */
    public function hasMore(): bool;

    /**
     * Check if a taxonomy term exists for the post.
     *
     * @param string|int|array $term
     * @param string $taxonomy
     *
     * @return bool
     */
    public function hasTerm($term, string $taxonomy): bool;

    /**
     * Check if post could be hierarchical.
     *
     * @return bool
     */
    public function isHierarchical(): bool;

    /**
     * Check if post is in post_type by its name.
     *
     * @param string $post_type
     *
     * @return bool
     */
    public function isType(string $post_type): bool;

    /**
     * Check if post is in one of post_types by their identifier names.
     *
     * @param string[] $post_types
     *
     * @return bool
     */
    public function typeIn(array $post_types): bool;
}