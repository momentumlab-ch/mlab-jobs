<?php
/**
 * Ausgabe: Stellenliste per Shortcode und Eckdaten auf der Stellenseite.
 *
 * @package MLab_Jobs
 */

defined( 'ABSPATH' ) || exit;

add_shortcode( 'mlab_jobs', 'mlab_jobs_shortcode' );
add_filter( 'the_content', 'mlab_jobs_single_content' );
add_action( 'wp_enqueue_scripts', 'mlab_jobs_register_style' );

/**
 * Stil nur anmelden, geladen wird er erst dort, wo eine Stelle oder die Liste erscheint.
 */
function mlab_jobs_register_style(): void {
	wp_register_style( 'mlab-jobs', MLAB_JOBS_URL . 'assets/css/mlab-jobs.css', array(), MLAB_JOBS_VERSION );
	if ( is_singular( 'mlab_job' ) ) {
		wp_enqueue_style( 'mlab-jobs' );
	}
}

/**
 * Offene Stellen, neueste zuerst. Abgelaufene fallen weg.
 *
 * @return WP_Post[]
 */
function mlab_jobs_open_jobs(): array {
	$jobs = get_posts(
		array(
			'post_type'   => 'mlab_job',
			'post_status' => 'publish',
			'numberposts' => 50,
		)
	);
	return array_values( array_filter( $jobs, fn( $job ) => ! mlab_jobs_is_expired( $job->ID ) ) );
}

/**
 * [mlab_jobs]: Liste der offenen Stellen.
 */
function mlab_jobs_shortcode(): string {
	wp_enqueue_style( 'mlab-jobs' );
	$jobs = mlab_jobs_open_jobs();
	if ( ! $jobs ) {
		return '<p class="mlab-jobs-empty">' . esc_html__( 'Zurzeit sind keine Stellen offen. Spontanbewerbungen sind willkommen.', 'mlab-jobs' ) . '</p>';
	}
	$items = array_map( 'mlab_jobs_card', $jobs );
	return '<ul class="mlab-jobs-list">' . implode( '', $items ) . '</ul>';
}

/**
 * Eine Karte in der Stellenliste.
 *
 * @param WP_Post $job Stelleninserat.
 */
function mlab_jobs_card( WP_Post $job ): string {
	return sprintf(
		'<li class="mlab-jobs-card"><h3><a href="%1$s">%2$s</a></h3><p class="mlab-jobs-meta">%3$s</p><p>%4$s</p><p><a class="mlab-jobs-more" href="%1$s">%5$s</a></p></li>',
		esc_url( get_permalink( $job ) ),
		esc_html( get_the_title( $job ) ),
		esc_html( mlab_jobs_meta_line( $job->ID ) ),
		esc_html( get_the_excerpt( $job ) ),
		esc_html__( 'Zur Stelle', 'mlab-jobs' )
	);
}

/**
 * Kurzzeile mit Pensum, Lohn und Ort.
 *
 * @param int $post_id Stelleninserat.
 */
function mlab_jobs_meta_line( int $post_id ): string {
	$parts = array( mlab_jobs_get( $post_id, 'workload' ), mlab_jobs_get( $post_id, 'salary' ), mlab_jobs_get( $post_id, 'location' ) );
	return implode( ' · ', array_filter( $parts ) );
}

/**
 * Auf der Stellenseite: Eckdaten vor dem Text, Bewerbungsknopf danach.
 *
 * @param string $content Inhalt.
 */
function mlab_jobs_single_content( string $content ): string {
	if ( ! is_singular( 'mlab_job' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	$post_id = get_the_ID();
	if ( mlab_jobs_is_expired( $post_id ) ) {
		return '<p class="mlab-jobs-closed">' . esc_html__( 'Diese Stelle ist nicht mehr ausgeschrieben.', 'mlab-jobs' ) . '</p>' . $content;
	}
	return mlab_jobs_facts( $post_id ) . $content . mlab_jobs_apply_button( $post_id );
}

/**
 * Eckdaten als Liste.
 *
 * @param int $post_id Stelleninserat.
 */
function mlab_jobs_facts( int $post_id ): string {
	$rows = array(
		__( 'Pensum', 'mlab-jobs' )         => mlab_jobs_get( $post_id, 'workload' ),
		__( 'Lohn', 'mlab-jobs' )           => mlab_jobs_get( $post_id, 'salary' ),
		__( 'Stellenantritt', 'mlab-jobs' ) => mlab_jobs_format_date( mlab_jobs_get( $post_id, 'start_date' ) ),
		__( 'Arbeitsort', 'mlab-jobs' )     => mlab_jobs_get( $post_id, 'location' ),
	);
	$html = '';
	foreach ( array_filter( $rows ) as $label => $value ) {
		$html .= sprintf( '<li><strong>%s</strong> %s</li>', esc_html( $label ), esc_html( $value ) );
	}
	return '<ul class="mlab-jobs-facts">' . $html . '</ul>';
}

/**
 * Knopf "Jetzt bewerben" als E-Mail mit Stellentitel im Betreff.
 *
 * @param int $post_id Stelleninserat.
 */
function mlab_jobs_apply_button( int $post_id ): string {
	$email = mlab_jobs_get( $post_id, 'apply_email' );
	if ( ! is_email( $email ) ) {
		return '';
	}
	/* translators: %s: Stellentitel */
	$subject = sprintf( __( 'Bewerbung: %s', 'mlab-jobs' ), get_the_title( $post_id ) );
	$href    = 'mailto:' . $email . '?subject=' . rawurlencode( $subject );
	return sprintf( '<p class="mlab-jobs-apply"><a class="wp-element-button" href="%s">%s</a></p>', esc_url( $href, array( 'mailto' ) ), esc_html__( 'Jetzt bewerben', 'mlab-jobs' ) );
}

/**
 * Datum im Format der Website, leer bleibt leer.
 *
 * @param string $date Datum als Y-m-d.
 */
function mlab_jobs_format_date( string $date ): string {
	$time = strtotime( $date );
	return $time ? wp_date( get_option( 'date_format' ), $time ) : '';
}
