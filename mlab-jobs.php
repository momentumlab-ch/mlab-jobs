<?php
/**
 * Plugin Name:       MomentumLab Jobs
 * Plugin URI:        https://github.com/momentumlab-ch/mlab-jobs
 * Description:       Stelleninserate erfassen und veröffentlichen, mit Stellenliste per Shortcode und Google-Jobs-Daten.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.0
 * Author:            MomentumLab
 * Author URI:        https://momentumlab.ch
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://github.com/momentumlab-ch/mlab-jobs
 * Text Domain:       mlab-jobs
 * Domain Path:       /languages
 *
 * @package MLab_Jobs
 */

defined( 'ABSPATH' ) || exit;

define( 'MLAB_JOBS_VERSION', '1.0.0' );
define( 'MLAB_JOBS_FILE', __FILE__ );
define( 'MLAB_JOBS_DIR', plugin_dir_path( __FILE__ ) );
define( 'MLAB_JOBS_URL', plugin_dir_url( __FILE__ ) );

require_once MLAB_JOBS_DIR . 'inc/options.php';
require_once MLAB_JOBS_DIR . 'inc/post-type.php';
require_once MLAB_JOBS_DIR . 'inc/meta-box.php';
require_once MLAB_JOBS_DIR . 'inc/frontend.php';
require_once MLAB_JOBS_DIR . 'inc/schema.php';
require_once MLAB_JOBS_DIR . 'inc/updater.php';

register_activation_hook( __FILE__, 'mlab_jobs_activate' );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

/**
 * Registriert den Inhaltstyp vor dem Neuaufbau der Permalinks, damit /jobs/<stelle>/ sofort funktioniert.
 */
function mlab_jobs_activate(): void {
	mlab_jobs_register_post_type();
	flush_rewrite_rules();
}

add_action( 'init', 'mlab_jobs_load_textdomain' );

/**
 * Lädt die Übersetzungen.
 */
function mlab_jobs_load_textdomain(): void {
	load_plugin_textdomain( 'mlab-jobs', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
