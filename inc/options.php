<?php
/**
 * Alle Felder eines Stelleninserats an einem Ort. Meta-Box, Ausgabe und Google-Jobs-Daten lesen nur von hier.
 *
 * @package MLab_Jobs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Felddefinitionen: Schlüssel => Bezeichnung, Typ, Standardwert.
 *
 * @return array<string, array<string, mixed>>
 */
function mlab_jobs_fields(): array {
	$fields = array(
		'employment_type' => array(
			'label'   => __( 'Anstellungsart', 'mlab-jobs' ),
			'type'    => 'select',
			'default' => 'PART_TIME',
			'choices' => mlab_jobs_employment_types(),
		),
		'workload'        => array(
			'label'   => __( 'Pensum', 'mlab-jobs' ),
			'type'    => 'text',
			'default' => __( 'Aushilfe', 'mlab-jobs' ),
		),
		'salary'          => array(
			'label'   => __( 'Lohn', 'mlab-jobs' ),
			'type'    => 'text',
			'default' => __( 'Stundenlohn', 'mlab-jobs' ),
		),
		'start_date'      => array(
			'label'   => __( 'Stellenantritt', 'mlab-jobs' ),
			'type'    => 'date',
			'default' => '',
		),
		'location'        => array(
			'label'   => __( 'Arbeitsort', 'mlab-jobs' ),
			'type'    => 'text',
			'default' => 'Grindelwald',
		),
		'postal_code'     => array(
			'label'   => __( 'Postleitzahl', 'mlab-jobs' ),
			'type'    => 'text',
			'default' => '3818',
		),
		'apply_email'     => array(
			'label'   => __( 'Bewerbung an (E-Mail)', 'mlab-jobs' ),
			'type'    => 'email',
			'default' => get_option( 'admin_email' ),
		),
		'valid_through'   => array(
			'label'   => __( 'Ausgeschrieben bis', 'mlab-jobs' ),
			'type'    => 'date',
			'default' => '',
		),
	);

	/**
	 * Felder eines Stelleninserats anpassen.
	 *
	 * @param array $fields Felddefinitionen.
	 */
	return apply_filters( 'mlab_jobs_fields', $fields );
}

/**
 * Anstellungsarten im Format von Google Jobs (schema.org employmentType).
 *
 * @return array<string, string>
 */
function mlab_jobs_employment_types(): array {
	return array(
		'PART_TIME' => __( 'Teilzeit / Aushilfe', 'mlab-jobs' ),
		'FULL_TIME' => __( 'Vollzeit', 'mlab-jobs' ),
		'TEMPORARY' => __( 'Befristet / Saison', 'mlab-jobs' ),
		'INTERN'    => __( 'Praktikum', 'mlab-jobs' ),
		'OTHER'     => __( 'Andere', 'mlab-jobs' ),
	);
}

/**
 * Wert eines Felds, mit Standardwert, wenn nichts gespeichert ist.
 *
 * @param int    $post_id Stelleninserat.
 * @param string $key     Feldschlüssel.
 */
function mlab_jobs_get( int $post_id, string $key ): string {
	$fields = mlab_jobs_fields();
	$value  = get_post_meta( $post_id, '_mlab_jobs_' . $key, true );
	if ( '' === $value && isset( $fields[ $key ] ) ) {
		return (string) $fields[ $key ]['default'];
	}
	return (string) $value;
}

/**
 * Ist das Inserat abgelaufen? Ohne Enddatum bleibt es offen.
 *
 * @param int $post_id Stelleninserat.
 */
function mlab_jobs_is_expired( int $post_id ): bool {
	$until = mlab_jobs_get( $post_id, 'valid_through' );
	return '' !== $until && $until < wp_date( 'Y-m-d' );
}
