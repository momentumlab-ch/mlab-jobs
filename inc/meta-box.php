<?php
/**
 * Eingabefelder im Editor eines Stelleninserats.
 *
 * @package MLab_Jobs
 */

defined( 'ABSPATH' ) || exit;

add_action( 'add_meta_boxes_mlab_job', 'mlab_jobs_add_meta_box' );
add_action( 'save_post_mlab_job', 'mlab_jobs_save_meta', 10, 2 );

/**
 * Box "Eckdaten der Stelle" neben dem Text.
 */
function mlab_jobs_add_meta_box(): void {
	add_meta_box( 'mlab-jobs-details', __( 'Eckdaten der Stelle', 'mlab-jobs' ), 'mlab_jobs_render_meta_box', 'mlab_job', 'normal', 'high' );
}

/**
 * Zeigt alle Felder aus inc/options.php.
 *
 * @param WP_Post $post Stelleninserat.
 */
function mlab_jobs_render_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'mlab_jobs_save', 'mlab_jobs_nonce' );
	echo '<table class="form-table" role="presentation"><tbody>';
	foreach ( mlab_jobs_fields() as $key => $field ) {
		$id = 'mlab-jobs-' . $key;
		printf( '<tr><th scope="row"><label for="%s">%s</label></th><td>', esc_attr( $id ), esc_html( $field['label'] ) );
		mlab_jobs_render_input( $id, $key, $field, mlab_jobs_get( $post->ID, $key ) );
		echo '</td></tr>';
	}
	echo '</tbody></table>';
	echo '<p class="description">' . esc_html__( 'Ohne Enddatum bleibt die Stelle online. Nach dem Enddatum verschwindet sie aus der Liste und aus Google Jobs.', 'mlab-jobs' ) . '</p>';
}

/**
 * Ein Eingabefeld.
 *
 * @param string $id    HTML-ID.
 * @param string $key   Feldschlüssel.
 * @param array  $field Felddefinition.
 * @param string $value Aktueller Wert.
 */
function mlab_jobs_render_input( string $id, string $key, array $field, string $value ): void {
	$name = 'mlab_jobs[' . $key . ']';
	if ( 'select' === $field['type'] ) {
		printf( '<select id="%s" name="%s">', esc_attr( $id ), esc_attr( $name ) );
		foreach ( $field['choices'] as $choice => $label ) {
			printf( '<option value="%s"%s>%s</option>', esc_attr( $choice ), selected( $value, $choice, false ), esc_html( $label ) );
		}
		echo '</select>';
		return;
	}
	printf( '<input type="%s" id="%s" name="%s" value="%s" class="regular-text">', esc_attr( $field['type'] ), esc_attr( $id ), esc_attr( $name ), esc_attr( $value ) );
}

/**
 * Speichert die Felder, nur mit gültiger Nonce und Bearbeitungsrecht.
 *
 * @param int     $post_id Stelleninserat.
 * @param WP_Post $post    Stelleninserat.
 */
function mlab_jobs_save_meta( int $post_id, WP_Post $post ): void {
	$nonce = isset( $_POST['mlab_jobs_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['mlab_jobs_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'mlab_jobs_save' ) || wp_is_post_autosave( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$input = isset( $_POST['mlab_jobs'] ) && is_array( $_POST['mlab_jobs'] ) ? wp_unslash( $_POST['mlab_jobs'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- jedes Feld wird unten einzeln nach Typ bereinigt.
	foreach ( mlab_jobs_fields() as $key => $field ) {
		$value = isset( $input[ $key ] ) ? mlab_jobs_sanitize( (string) $input[ $key ], $field ) : '';
		update_post_meta( $post_id, '_mlab_jobs_' . $key, $value );
	}
}

/**
 * Bereinigt einen Wert nach Feldtyp.
 *
 * @param string $value Eingabe.
 * @param array  $field Felddefinition.
 */
function mlab_jobs_sanitize( string $value, array $field ): string {
	switch ( $field['type'] ) {
		case 'email':
			return sanitize_email( $value );
		case 'date':
			return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ? $value : '';
		case 'select':
			return array_key_exists( $value, $field['choices'] ) ? $value : (string) $field['default'];
		default:
			return sanitize_text_field( $value );
	}
}
