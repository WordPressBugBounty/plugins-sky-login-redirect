<?php
/**
 * Data Migration for PERF-005: Select2 to Carbon Fields Association Fields
 *
 * This script migrates data from the old Select2/multiselect format to the new
 * Carbon Fields native association field format.
 *
 * Old format:
 * - Users: ['John Doe (ID=5)'] or ['John Doe (ID=5)', 'Jane Smith (ID=10)']
 * - Pages: 123 or [123, 456]
 *
 * New format:
 * - Users: [['id' => '5', 'type' => 'user', 'value' => 'user:5', 'title' => 'John Doe']]
 * - Pages: [['id' => '123', 'type' => 'post', 'subtype' => 'page', 'value' => 'post:page:123', 'title' => 'Page Title']]
 *
 * @category Migration
 * @package  Sky_Login_Redirect
 * @author   Utopique <support@utopique.net>
 * @license  GPL https://utopique.net
 * @link     https://utopique.net
 * @since    4.1.9
 */

declare(strict_types=1);

namespace SkyLoginRedirect\Migration;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Data migrator for association field conversion.
 */
final class AssociationFieldMigrator
{
    /**
     * Option name to track migration status.
     */
    private const MIGRATION_OPTION = 'slr_perf005_migration_completed';

    /**
     * Migration version for tracking.
     */
    private const MIGRATION_VERSION = '4190';

    /**
     * Run the migration if not already completed.
     *
     * @return void
     */
    public static function run(): void
    {
        // Check if migration already completed
        $completed = get_option(self::MIGRATION_OPTION, '0');
        if (version_compare($completed, self::MIGRATION_VERSION, '>=')) {
            return;
        }

        $migrator = new self();
        $migrator->migrateAll();

        // Mark migration as complete
        update_option(self::MIGRATION_OPTION, self::MIGRATION_VERSION);
    }

    /**
     * Run all migrations.
     *
     * @return void
     */
    private function migrateAll(): void
    {
        // Get all redirect rules from the slr_xlogin_logout complex field
        $this->migrateRedirectRules();

        // Get all restriction rules from the slr_xrestrict complex field
        $this->migrateRestrictionRules();
    }

    /**
     * Migrate redirect rules in slr_xlogin_logout complex field.
     *
     * @return void
     */
    private function migrateRedirectRules(): void
    {
        // Get all redirect rules
        $rules = carbon_get_theme_option('slr_xlogin_logout');
        if (empty($rules) || !is_array($rules)) {
            return;
        }

        $updated = false;

        foreach ($rules as $index => $rule) {
            // Migrate slr_xuser field
            if (!empty($rule['slr_xuser']) && is_array($rule['slr_xuser'])) {
                $new_users = $this->migrateUserData($rule['slr_xuser']);
                if ($new_users !== $rule['slr_xuser']) {
                    carbon_set_theme_option("slr_xlogin_logout[{$index}]/slr_xuser", $new_users);
                    $updated = true;
                }
            }

            // Migrate slr_xlogin_page field
            if (!empty($rule['slr_xlogin_page'])) {
                $new_page = $this->migratePageData($rule['slr_xlogin_page']);
                if ($new_page !== $rule['slr_xlogin_page']) {
                    carbon_set_theme_option("slr_xlogin_logout[{$index}]/slr_xlogin_page", $new_page);
                    $updated = true;
                }
            }

            // Migrate slr_xlogout_page field
            if (!empty($rule['slr_xlogout_page'])) {
                $new_page = $this->migratePageData($rule['slr_xlogout_page']);
                if ($new_page !== $rule['slr_xlogout_page']) {
                    carbon_set_theme_option("slr_xlogin_logout[{$index}]/slr_xlogout_page", $new_page);
                    $updated = true;
                }
            }
        }

        if ($updated) {
            // Clear any cached data
            wp_cache_delete('slr_cached_options', 'slr');
        }
    }

    /**
     * Migrate restriction rules in slr_xrestrict complex field.
     *
     * @return void
     */
    private function migrateRestrictionRules(): void
    {
        // Get all restriction rules
        $rules = carbon_get_theme_option('slr_xrestrict');
        if (empty($rules) || !is_array($rules)) {
            return;
        }

        $updated = false;

        foreach ($rules as $index => $rule) {
            // Migrate slr_xcpt_restrict field (posts/pages)
            if (!empty($rule['slr_xcpt_restrict']) && is_array($rule['slr_xcpt_restrict'])) {
                $new_posts = $this->migratePostData($rule['slr_xcpt_restrict']);
                if ($new_posts !== $rule['slr_xcpt_restrict']) {
                    carbon_set_theme_option("slr_xrestrict[{$index}]/slr_xcpt_restrict", $new_posts);
                    $updated = true;
                }
            }

            // Migrate slr_xuser_restrict field
            if (!empty($rule['slr_xuser_restrict']) && is_array($rule['slr_xuser_restrict'])) {
                $new_users = $this->migrateUserData($rule['slr_xuser_restrict']);
                if ($new_users !== $rule['slr_xuser_restrict']) {
                    carbon_set_theme_option("slr_xrestrict[{$index}]/slr_xuser_restrict", $new_users);
                    $updated = true;
                }
            }
        }

        if ($updated) {
            // Clear any cached data
            wp_cache_delete('slr_restrict_rules', 'slr');
        }
    }

    /**
     * Migrate user data from old format to new association format.
     *
     * Old: ['John Doe (ID=5)', 'Jane Smith (ID=10)']
     * New: [
     *     ['id' => '5', 'type' => 'user', 'value' => 'user:5', 'title' => 'John Doe'],
     *     ['id' => '10', 'type' => 'user', 'value' => 'user:10', 'title' => 'Jane Smith']
     * ]
     *
     * @param array $users Old format user data.
     * @return array New format user data.
     */
    private function migrateUserData(array $users): array
    {
        $migrated = [];

        foreach ($users as $user) {
            // Already in new format
            if (is_array($user) && isset($user['id'], $user['type'])) {
                $migrated[] = $user;
                continue;
            }

            // Old format: "Display Name (ID=123)"
            if (is_string($user) && preg_match('/\(ID=(\d+)\)/', $user, $matches)) {
                $user_id = $matches[1];
                $user_obj = get_user_by('id', (int) $user_id);

                if ($user_obj) {
                    $migrated[] = [
                        'id'    => (string) $user_id,
                        'type'  => 'user',
                        'value' => "user:{$user_id}",
                        'title' => $user_obj->display_name,
                    ];
                }

                continue;
            }

            // Legacy format fallback: just an ID
            if (is_numeric($user)) {
                $user_id = (int) $user;
                $user_obj = get_user_by('id', $user_id);

                if ($user_obj) {
                    $migrated[] = [
                        'id'    => (string) $user_id,
                        'type'  => 'user',
                        'value' => "user:{$user_id}",
                        'title' => $user_obj->display_name,
                    ];
                }
            }
        }

        return $migrated;
    }

    /**
     * Migrate page data from old format to new association format.
     *
     * Old: 123 or [123, 456]
     * New: [
     *     ['id' => '123', 'type' => 'post', 'subtype' => 'page', 'value' => 'post:page:123', 'title' => 'Page Title']
     * ]
     *
     * @param mixed $pages Old format page data (int, array of ints, or already migrated array).
     * @return array New format page data.
     */
    private function migratePageData($pages): array
    {
        // Handle single page ID (legacy format)
        if (is_numeric($pages)) {
            $page_id = (int) $pages;
            $page = get_post($page_id);

            if ($page && $page->post_type === 'page') {
                return [
                    [
                        'id'      => (string) $page_id,
                        'type'    => 'post',
                        'subtype' => 'page',
                        'value'   => "post:page:{$page_id}",
                        'title'   => $page->post_title,
                    ],
                ];
            }

            return [];
        }

        // Handle array of page IDs or already migrated data
        if (is_array($pages)) {
            // Check if already in new format
            if (!empty($pages) && is_array($pages[0]) && isset($pages[0]['id'], $pages[0]['type'])) {
                return $pages;
            }

            $migrated = [];

            foreach ($pages as $page_id) {
                if (!is_numeric($page_id)) {
                    continue;
                }

                $page_id = (int) $page_id;
                $page = get_post($page_id);

                if ($page && $page->post_type === 'page') {
                    $migrated[] = [
                        'id'      => (string) $page_id,
                        'type'    => 'post',
                        'subtype' => 'page',
                        'value'   => "post:page:{$page_id}",
                        'title'   => $page->post_title,
                    ];
                }
            }

            return $migrated;
        }

        return [];
    }

    /**
     * Migrate post data from old format to new association format.
     *
     * Similar to migratePageData but handles post/page types from slr_xcpt_restrict.
     *
     * @param array $posts Old format post data.
     * @return array New format post data.
     */
    private function migratePostData(array $posts): array
    {
        $migrated = [];

        foreach ($posts as $post) {
            // Already in new format
            if (is_array($post) && isset($post['id'], $post['type'])) {
                $migrated[] = $post;
                continue;
            }

            // Old format: "Post Title (ID=123)"
            if (is_string($post) && preg_match('/\(ID=(\d+)\)/', $post, $matches)) {
                $post_id = (int) $matches[1];
                $post_obj = get_post($post_id);

                if ($post_obj) {
                    $migrated[] = [
                        'id'      => (string) $post_id,
                        'type'    => 'post',
                        'subtype' => $post_obj->post_type,
                        'value'   => "post:{$post_obj->post_type}:{$post_id}",
                        'title'   => $post_obj->post_title,
                    ];
                }

                continue;
            }

            // Legacy format: just an ID
            if (is_numeric($post)) {
                $post_id = (int) $post;
                $post_obj = get_post($post_id);

                if ($post_obj) {
                    $migrated[] = [
                        'id'      => (string) $post_id,
                        'type'    => 'post',
                        'subtype' => $post_obj->post_type,
                        'value'   => "post:{$post_obj->post_type}:{$post_id}",
                        'title'   => $post_obj->post_title,
                    ];
                }
            }
        }

        return $migrated;
    }

    /**
     * Check if migration is needed.
     *
     * @return bool True if migration needs to run.
     */
    public static function isMigrationNeeded(): bool
    {
        $completed = get_option(self::MIGRATION_OPTION, '0');
        return version_compare($completed, self::MIGRATION_VERSION, '<');
    }

    /**
     * Reset migration status (for testing purposes).
     *
     * @return void
     */
    public static function reset(): void
    {
        delete_option(self::MIGRATION_OPTION);
    }
}

/**
 * Hook the migration to run on plugin update (admin_init only when version changes).
 *
 * @return void
 */
function run_perf005_migration(): void
{
    // Only run in admin context when Carbon Fields is available
    if (!is_admin()) {
        return;
    }

    // Check if function exists (Carbon Fields might not be loaded yet)
    if (!function_exists('carbon_get_theme_option')) {
        return;
    }

    // Check if migration is actually needed before running
    if (!AssociationFieldMigrator::isMigrationNeeded()) {
        return;
    }

    AssociationFieldMigrator::run();
}
add_action('admin_init', __NAMESPACE__ . '\\run_perf005_migration', 100); // Run after Carbon Fields is loaded

/**
 * Alternative: Hook to plugin upgrade process for immediate migration on update.
 *
 * @param WP_Upgrader $upgrader_object Upgrader instance.
 * @param array       $options         Upgrade options.
 * @return void
 */
function run_perf005_migration_on_upgrade($upgrader_object, $options): void
{
    // Check if this is our plugin being updated
    if (($options['action'] ?? '') === 'update' && ($options['type'] ?? '') === 'plugin') {
        // Check if our plugin is in the list of updated plugins
        $plugins = $options['plugins'] ?? [];
        if (in_array('sky-login-redirect/sky-login-redirect.php', (array) $plugins, true)) {
            // Schedule a one-time event to run migration after the update is complete
            if (!wp_next_scheduled('slr_run_migration')) {
                wp_schedule_single_event(time() + 5, 'slr_run_migration');
            }
        }
    }
}
add_action('upgrader_process_complete', __NAMESPACE__ . '\\run_perf005_migration_on_upgrade', 10, 2);

/**
 * Manual migration trigger via WP-CLI or admin action.
 *
 * Usage: add_action('admin_init', function() { do_action('slr_run_migration'); });
 *
 * @return void
 */
function manual_migration_trigger(): void
{
    AssociationFieldMigrator::reset();
    AssociationFieldMigrator::run();
}
add_action('slr_run_migration', __NAMESPACE__ . '\\manual_migration_trigger');
