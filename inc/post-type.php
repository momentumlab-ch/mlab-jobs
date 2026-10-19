<?php
/**
 * Inhaltstyp "Stelle".
 *
 * @package MLab_Jobs
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'mlab_jobs_register_post_type' );

/**
 * Stelleninserate unter /jobs/<stelle>/. Die Übersicht ist eine normale Seite mit dem Shortcode [mlab_jobs].
 */
function mlab_jobs_register_post_type(): void {
	register_post_type(
		'mlab_job',
		array(
			'labels'       => array(
				'name'               => __( 'Stellen', 'mlab-jobs' ),
				'singular_name'      => __( 'Stelle', 'mlab-jobs' ),
				'add_new'            => __( 'Stelle erfassen', 'mlab-jobs' ),
				'add_new_item'       => __( 'Neue Stelle erfassen', 'mlab-jobs' ),
				'edit_item'          => __( 'Stelle bearbeiten', 'mlab-jobs' ),
				'view_item'          => __( 'Stelle ansehen', 'mlab-jobs' ),
				'all_items'          => __( 'Alle Stellen', 'mlab-jobs' ),
				'search_items'       => __( 'Stellen durchsuchen', 'mlab-jobs' ),
				'not_found'          => __( 'Keine Stellen gefunden.', 'mlab-jobs' ),
				'not_found_in_trash' => __( 'Keine Stellen im Papierkorb.', 'mlab-jobs' ),
			),
			'public'       => true,
			'has_archive'  => false,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-businessperson',
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
			'rewrite'      => array(
				'slug'       => _x( 'jobs', 'URL-Teil der Stelleninserate', 'mlab-jobs' ),
				'with_front' => false,
			),
		)
	);
}
