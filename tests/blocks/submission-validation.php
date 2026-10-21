<?php
/** Read-only submission checks: wp eval-file; HTTP responses are mocked. */
use WordPressTV\Submissions;

$checks = 0;
$expect = static function ( $condition, $label ) use ( &$checks ) {
	if ( ! $condition ) {
		throw new RuntimeException( $label );
	}
	++$checks;
};
$saved_post = $_POST;
$mock = null;
$requests = 0;
$http = static function ( $pre, $args, $url ) use ( &$mock, &$requests, $expect ) {
	if ( ! str_starts_with( $url, 'https://wordpress.org/grav-redirect.php?' ) ) {
		return $pre;
	}
	++$requests;
	$expect( 0 === $args['redirection'], 'Must inspect redirect rather than follow it.' );
	return $mock;
};
add_filter( 'pre_http_request', $http, 10, 3 );
try {
	foreach ( array(
		array( array( 'headers' => array( 'location' => 'https://secure.gravatar.com/avatar/test?d=retro' ) ), true ),
		array( array( 'headers' => array( 'location' => 'https://secure.gravatar.com/avatar/test?d=mm' ) ), false ),
		array( array( 'headers' => array() ), false ),
		array( new WP_Error( 'http_request_failed', 'Simulated network error' ), false ),
	) as $case ) {
		$mock = $case[0];
		$expect( $case[1] === Submissions\wporg_username_exists( 'test-user' ), 'Username result must match legacy behavior.' );
	}
	$_POST = array( 'wptv_honey' => '', 'wptv_producer_username' => 'invalid-test-user', 'wptv_uploaded_by' => 'Test', 'wptv_email' => 'test@example.com' );
	$expect( 14 === Submissions\validate(), 'Producer validation must not be skipped when unavailable.' );
	$_POST['wptv_producer_username'] = '';
	$before = $requests;
	$expect( false === Submissions\validate(), 'Blank optional producer accepted.' );
	$expect( $before === $requests, 'Blank producer must not trigger HTTP.' );
	$mock = array( 'headers' => array( 'location' => 'https://secure.gravatar.com/avatar/test?d=retro' ) );
	$_POST['wptv_producer_username'] = 'test-user';
	$expect( false === Submissions\validate(), 'Existing producer accepted.' );
	echo "PASS: $checks checks; username redirects, missing headers, network errors and mandatory producer validation. No data changed.\n";
} finally {
	$_POST = $saved_post;
	remove_filter( 'pre_http_request', $http, 10 );
}
