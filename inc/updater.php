<?php
/**
 * Updates über GitHub Releases (Plugin Update Checker v5).
 *
 * Die Quelle steht nur hier. Der Header "Update URI" in der Hauptdatei nennt dieselbe URL.
 *
 * @package MLab_Jobs
 */

defined( 'ABSPATH' ) || exit;

require_once dirname( __DIR__ ) . '/lib/plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

add_action( 'init', 'mlab_jobs_register_updater' );

/**
 * Registriert die Update-Prüfung. Private Repos brauchen die Token-Konstante in wp-config.php.
 */
function mlab_jobs_register_updater(): void {
	$private = false;
	$token   = defined( 'MLAB_JOBS_GITHUB_TOKEN' ) ? (string) constant( 'MLAB_JOBS_GITHUB_TOKEN' ) : '';

	if ( $private && '' === $token ) {
		add_action( 'admin_notices', 'mlab_jobs_missing_token_notice' );
		return;
	}

	$checker = PucFactory::buildUpdateChecker(
		'https://github.com/momentumlab-ch/mlab-jobs/',
		dirname( __DIR__ ) . '/mlab-jobs.php',
		'mlab-jobs'
	);
	$checker->setBranch( 'main' );

	$api = $checker->getVcsApi();
	// 2 = Api::REQUIRE_RELEASE_ASSETS: ohne passendes ZIP kein Update, nie der GitHub-Quellcode.
	$api->enableReleaseAssets( '/^mlab-jobs\.zip$/', 2 );
	if ( $private ) {
		$api->setAuthentication( $token );
	}
}

/**
 * Hinweis nur für Admins, wenn bei einem privaten Repo die Token-Konstante fehlt.
 */
function mlab_jobs_missing_token_notice(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	printf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		esc_html__( 'Updates sind nicht aktiv: In wp-config.php fehlt die Konstante MLAB_JOBS_GITHUB_TOKEN.', 'mlab-jobs' )
	);
}
