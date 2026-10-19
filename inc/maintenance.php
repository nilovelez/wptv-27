<?php
/**
 * Maintenance helpers.
 *
 * @package WordPressTV
 */

namespace WordPressTV\Maintenance;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Flushes rewrite rules when a super admin visits any URL with ?flush_rules=1.
 *
 * Kept from wptv2, because there is no other way to flush rules on production
 * without a deploy. wptv2 also allowed one hardcoded user ID; that exception is
 * dropped (docs/decisions.md). Whether this is still needed is an open question.
 */
function maybe_flush_rewrite_rules() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Idempotent maintenance action, restricted to super admins.
	if ( empty( $_GET['flush_rules'] ) || ! is_super_admin() ) {
		return;
	}

	flush_rewrite_rules();
	exit( 'Rewrite rules flushed.' );
}
add_action( 'init', __NAMESPACE__ . '\maybe_flush_rewrite_rules', 99 );
