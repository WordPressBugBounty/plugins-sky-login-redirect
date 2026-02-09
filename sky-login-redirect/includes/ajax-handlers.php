<?php
/**
 * AJAX Handlers for Sky Login Redirect
 *
 * Modern PHP 8.4+ implementation with strategy pattern.
 *
 * @package Sky_Login_Redirect
 */

declare(strict_types=1);

namespace SkyLoginRedirect\Ajax;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Search type enumeration.
 */
enum SearchType: string {
    case PAGES = 'pages';
    case USERS = 'users';
}

/**
 * Search strategy interface.
 */
interface SearchStrategy {
    /**
     * Execute search and return formatted results.
     *
     * @param string $search Search term.
     * @param int    $page   Page number.
     * @return array{results: array, pagination: array}
     */
    public function search( string $search, int $page ): array;
}

/**
 * Page search strategy.
 */
final class PageSearchStrategy implements SearchStrategy {
    public function __construct(
        private int $perPage = 30,
        private string $postType = 'page'
    ) {}

    public function search( string $search, int $page ): array {
        $args = [
            'post_type'      => $this->resolvePostType(),
            'post_status'    => 'publish',
            'posts_per_page' => $this->perPage,
            'paged'          => $page,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ];

        if ( $search !== '' ) {
            $args['s'] = $search;
        }

        $query = new \WP_Query( $args );
        $results = [];

        if ( $query->have_posts() ) {
            foreach ( $query->posts as $post ) {
                $results[] = [
                    'id'   => $post->ID,
                    'text' => sprintf(
                        '%s (ID=%d)',
                        esc_html( $post->post_title ?: __( '(no title)', 'sky-login-redirect' ) ),
                        $post->ID
                    ),
                ];
            }
        }

        return [
            'results'    => $results,
            'pagination' => [
                'more' => $page < $query->max_num_pages,
            ],
        ];
    }

    private function resolvePostType(): array|string {
        return match ( $this->postType ) {
            'any' => [ 'post', 'page' ],
            default => $this->postType,
        };
    }
}

/**
 * User search strategy.
 */
final class UserSearchStrategy implements SearchStrategy {
    public function __construct(
        private int $perPage = 30
    ) {}

    public function search( string $search, int $page ): array {
        $offset = ( $page - 1 ) * $this->perPage;

        $args = [
            'number'  => $this->perPage,
            'offset'  => $offset,
            'orderby' => 'display_name',
            'order'   => 'ASC',
        ];

        if ( $search !== '' ) {
            $args['search']         = '*' . $search . '*';
            $args['search_columns'] = [ 'user_login', 'user_email', 'display_name' ];
        }

        $user_query  = new \WP_User_Query( $args );
        $users       = $user_query->get_results();
        $total_users = $user_query->get_total();

        $results = [];
        foreach ( $users as $user ) {
            $results[] = [
                'id'   => esc_html( $user->display_name ),
                'text' => sprintf(
                    '%s (ID=%d)',
                    esc_html( $user->display_name ),
                    $user->ID
                ),
            ];
        }

        return [
            'results'    => $results,
            'pagination' => [
                'more' => ( $offset + $this->perPage ) < $total_users,
            ],
        ];
    }
}

/**
 * AJAX search handler with strategy pattern.
 */
final class AjaxSearchHandler {
    private const NONCE_ACTION = 'slr_ajax_nonce';
    private const CAPABILITY   = 'manage_options';
    private const RATE_LIMIT   = 100; // Max requests per minute
    private const RATE_WINDOW  = 60;  // Seconds

    public function __construct(
        private SearchStrategy $strategy
    ) {}

    /**
     * Handle AJAX search request.
     */
    public function handle(): void {
        check_ajax_referer( self::NONCE_ACTION, 'nonce' );

        if ( ! current_user_can( self::CAPABILITY ) ) {
            wp_send_json_error(
                [ 'message' => __( 'Insufficient permissions', 'sky-login-redirect' ) ],
                403
            );
            return;
        }

        // Rate limiting check
        if ( ! $this->checkRateLimit() ) {
            $this->logRequest( 'RATE_LIMITED' );
            wp_send_json_error(
                [ 'message' => __( 'Too many requests. Please try again later.', 'sky-login-redirect' ) ],
                429
            );
            return;
        }

        $search = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : '';
        $page   = isset( $_GET['page'] ) ? absint( $_GET['page'] ) : 1;

        $this->logRequest( 'SUCCESS', $search );

        $result = $this->strategy->search( $search, $page );
        wp_send_json_success( $result );
    }

    /**
     * Check if user has exceeded rate limit.
     *
     * @return bool True if within limit, false if exceeded.
     */
    private function checkRateLimit(): bool {
        $user_id = get_current_user_id();
        $key     = 'slr_ajax_rate_' . $user_id;
        $attempts = (int) get_transient( $key );

        if ( $attempts >= self::RATE_LIMIT ) {
            return false;
        }

        set_transient( $key, $attempts + 1, self::RATE_WINDOW );
        return true;
    }

    /**
     * Log AJAX request for debugging.
     *
     * @param string $status Request status.
     * @param string $search Search term (optional).
     */
    private function logRequest( string $status, string $search = '' ): void {
        if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
            return;
        }

        $strategy_type = match ( true ) {
            $this->strategy instanceof PageSearchStrategy => 'pages',
            $this->strategy instanceof UserSearchStrategy => 'users',
            default => 'unknown',
        };
    }

    /**
     * Create handler for page searches.
     */
    public static function forPages( ?string $postType = null ): self {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only operation, nonce verified in handle() method
        $postType = $postType ?? ( isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : 'page' );

        $allowed = [ 'page', 'post', 'any' ];
        if ( ! in_array( $postType, $allowed, true ) ) {
            $postType = 'page';
        }

        return new self( new PageSearchStrategy( postType: $postType ) );
    }

    /**
     * Create handler for user searches.
     */
    public static function forUsers(): self {
        return new self( new UserSearchStrategy() );
    }
}

/**
 * AJAX endpoint for page search.
 */
function ajax_search_pages(): void {
    AjaxSearchHandler::forPages()->handle();
}
add_action( 'wp_ajax_slr_search_pages', __NAMESPACE__ . '\\ajax_search_pages' );

/**
 * AJAX endpoint for user search.
 */
function ajax_search_users(): void {
    AjaxSearchHandler::forUsers()->handle();
}
add_action( 'wp_ajax_slr_search_users', __NAMESPACE__ . '\\ajax_search_users' );

/**
 * Filter Carbon Fields HTML to add AJAX select classes
 *
 * @param string $html  The field HTML.
 * @param object $field The field object.
 * @return string Modified HTML.
 */
function carbon_fields_ajax_select_filter( $html, $field ): string {
    $field_name = method_exists( $field, 'get_base_name' ) ? $field->get_base_name() : '';

    // Page selectors
    $page_fields = [ 'slr_xlogin_page', 'slr_xlogout_page' ];
    if ( in_array( $field_name, $page_fields, true ) ) {
        $html = str_replace(
            '<select',
            '<select class="slr-ajax-page-select" data-post-type="page"',
            $html
        );
    }

    // Post/Page selector for content restriction
    if ( 'slr_xcpt_restrict' === $field_name ) {
        $html = str_replace(
            '<select',
            '<select class="slr-ajax-page-select" data-post-type="any"',
            $html
        );
    }

    // User selector
    if ( 'slr_xuser' === $field_name ) {
        $html = str_replace(
            '<select',
            '<select class="slr-ajax-user-select"',
            $html
        );
    }

    return $html;
}
add_filter( 'carbon_fields_field_html', __NAMESPACE__ . '\\carbon_fields_ajax_select_filter', 10, 2 );
