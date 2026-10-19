<?php
/**
 * Google-Jobs-Daten (schema.org JobPosting) auf der Stellenseite.
 *
 * @package MLab_Jobs
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', 'mlab_jobs_output_schema' );

/**
 * JSON-LD nur für offene Stellen. Abgelaufene Stellen dürfen laut Google keine JobPosting-Daten mehr haben.
 */
function mlab_jobs_output_schema(): void {
	if ( ! is_singular( 'mlab_job' ) ) {
		return;
	}
	$post_id = get_queried_object_id();
	if ( mlab_jobs_is_expired( $post_id ) ) {
		return;
	}

	/**
	 * JobPosting-Daten vor der Ausgabe anpassen.
	 *
	 * @param array $data    Strukturierte Daten.
	 * @param int   $post_id Stelleninserat.
	 */
	$data = apply_filters( 'mlab_jobs_schema', mlab_jobs_schema_data( $post_id ), $post_id );
	printf( '<script type="application/ld+json">%s</script>' . "\n", wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
}

/**
 * Baut die JobPosting-Daten.
 *
 * @param int $post_id Stelleninserat.
 */
function mlab_jobs_schema_data( int $post_id ): array {
	$data = array(
		'@context'           => 'https://schema.org',
		'@type'              => 'JobPosting',
		'title'              => get_the_title( $post_id ),
		'description'        => wp_kses_post( apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) ) ),
		'datePosted'         => get_the_date( 'Y-m-d', $post_id ),
		'employmentType'     => mlab_jobs_get( $post_id, 'employment_type' ),
		'hiringOrganization' => mlab_jobs_hiring_organization(),
		'jobLocation'        => array(
			'@type'   => 'Place',
			'address' => array(
				'@type'           => 'PostalAddress',
				'addressLocality' => mlab_jobs_get( $post_id, 'location' ),
				'postalCode'      => mlab_jobs_get( $post_id, 'postal_code' ),
				'addressCountry'  => 'CH',
			),
		),
	);
	$until = mlab_jobs_get( $post_id, 'valid_through' );
	if ( '' !== $until ) {
		$data['validThrough'] = $until . 'T23:59:59';
	}
	return $data;
}

/**
 * Arbeitgeber aus den Website-Einstellungen.
 */
function mlab_jobs_hiring_organization(): array {
	$org  = array(
		'@type'  => 'Organization',
		'name'   => get_bloginfo( 'name' ),
		'sameAs' => home_url( '/' ),
	);
	$logo = get_site_icon_url( 512 );
	if ( $logo ) {
		$org['logo'] = $logo;
	}
	return $org;
}
