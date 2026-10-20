<?php
/**
 * Read-only general listing and page template checks; run with eval-file.
 *
 * @package WordPressTV
 */

if ( 'wptv-27' !== get_stylesheet() ) {
	WP_CLI::error( 'Activate wptv-27 first.' );
}
$checks = 0;
$assert = static function ( $condition, $message ) use ( &$checks ) {
	if ( ! $condition ) {
		WP_CLI::error( $message );
	}
	++$checks;
	WP_CLI::log( 'PASS ' . $message );
};
$registry = WP_Block_Patterns_Registry::get_instance();
foreach ( array( 'wptv/video-grid', 'wptv/search', 'wptv/404' ) as $slug ) {
	$pattern = $registry->get_registered( $slug );
	$assert( $pattern && ! $pattern['inserter'], $slug . ' is registered and hidden.' );
	$assert( false === strpos( $pattern['content'], '<?php' ), $slug . ' has evaluated translation strings.' );
}
$grid = $registry->get_registered( 'wptv/video-grid' );
$blocks = parse_blocks( $grid['content'] );
$query = array_values( array_filter( $blocks, static function ( $block ) { return 'core/query' === $block['blockName']; } ) )[0];
$assert( array( 'inherit' => true ) === $query['attrs']['query'], 'Grid inherits the main query without page-size, offset or ordering overrides.' );
$assert( false === strpos( $grid['content'], 'wp:html' ), 'Grid has no raw HTML blocks.' );
$assert( false !== strpos( $grid['content'], 'wp:query-no-results' ), 'Grid has an editable empty-results message.' );
$assert( false !== strpos( $grid['content'], 'wp:query-pagination-numbers' ), 'Grid retains native numbered pagination.' );
foreach ( array( 'archive', 'page', 'search', '404' ) as $name ) {
	$content = file_get_contents( get_theme_file_path( 'templates/' . $name . '.html' ) );
	$assert( false !== strpos( $content, '"slug":"header"' ) && false !== strpos( $content, '"slug":"footer"' ), $name . ' reuses the shared header and footer.' );
	$assert( false === strpos( $content, 'wp:html' ) && false === strpos( $content, '<?php' ), $name . ' contains only block template markup.' );
}
$page = file_get_contents( get_theme_file_path( 'templates/page.html' ) );
$assert( 1 === substr_count( $page, 'wp:post-title ' ) && 1 === substr_count( $page, 'wp:post-content ' ), 'Page renders one title and the unchanged stored content.' );
WP_CLI::success( $checks . ' general template checks passed; no data was written.' );
