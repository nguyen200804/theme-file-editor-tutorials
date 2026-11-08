<?php
/**
 * Custom Fields cho Taxonomy / Category (Term Meta)
 *
 * Cho phép đăng ký custom field cho taxonomy không cần plugin ACF.
 *
 * @package Theme_File_Editor_Tutorials
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $ten_registered_term_fields;
$ten_registered_term_fields = array();

/**
 * Đăng ký field cho taxonomy
 *
 * @param array $args Cấu hình term fields
 */
function ten_add_term_fields( $args ) {
    global $ten_registered_term_fields;

    $defaults = array(
        'taxonomies' => array( 'category' ),
        'fields'     => array(),
    );

    $group = wp_parse_args( $args, $defaults );
    $ten_registered_term_fields[] = $group;
}

/**
 * Đăng ký form fields cho taxonomy
 */
add_action( 'init', function () {
    global $ten_registered_term_fields;

    foreach ( $ten_registered_term_fields as $group ) {
        foreach ( (array) $group['taxonomies'] as $taxonomy ) {

            // Form thêm mới term
            add_action( "{$taxonomy}_add_form_fields", function () use ( $group ) {
                wp_nonce_field( 'ten_save_term_meta', 'ten_term_meta_nonce' );
                foreach ( $group['fields'] as $field ) {
                    echo '<div class="form-field term-' . esc_attr( $field['key'] ) . '-wrap">';
                    echo '<label for="' . esc_attr( $field['key'] ) . '">' . esc_html( $field['label'] ) . '</label>';
                    if ( function_exists( 'ten_render_field_input' ) ) {
                        ten_render_field_input( $field, '' );
                    }
                    echo '</div>';
                }
            } );

            // Form chỉnh sửa term
            add_action( "{$taxonomy}_edit_form_fields", function ( $term ) use ( $group ) {
                wp_nonce_field( 'ten_save_term_meta', 'ten_term_meta_nonce' );
                foreach ( $group['fields'] as $field ) {
                    $val = get_term_meta( $term->term_id, $field['key'], true );
                    echo '<tr class="form-field term-' . esc_attr( $field['key'] ) . '-wrap">';
                    echo '<th scope="row"><label for="' . esc_attr( $field['key'] ) . '">' . esc_html( $field['label'] ) . '</label></th>';
                    echo '<td>';
                    if ( function_exists( 'ten_render_field_input' ) ) {
                        ten_render_field_input( $field, $val );
                    }
                    echo '</td>';
                    echo '</tr>';
                }
            } );

            // Lưu dữ liệu term meta
            $save_callback = function ( $term_id ) use ( $group ) {
                if ( ! isset( $_POST['ten_term_meta_nonce'] ) || ! wp_verify_nonce( $_POST['ten_term_meta_nonce'], 'ten_save_term_meta' ) ) {
                    return;
                }

                if ( ! current_user_can( 'edit_term', $term_id ) ) {
                    return;
                }

                foreach ( $group['fields'] as $field ) {
                    $key = $field['key'];
                    $type = isset( $field['type'] ) ? $field['type'] : 'text';

                    if ( 'checkbox' === $type ) {
                        $val = isset( $_POST[ $key ] ) ? '1' : '0';
                        update_term_meta( $term_id, $key, $val );
                        continue;
                    }

                    if ( ! isset( $_POST[ $key ] ) ) {
                        delete_term_meta( $term_id, $key );
                        continue;
                    }

                    $raw_value = $_POST[ $key ];
                    if ( 'image' === $type ) {
                        update_term_meta( $term_id, $key, absint( $raw_value ) );
                    } elseif ( 'textarea' === $type ) {
                        update_term_meta( $term_id, $key, sanitize_textarea_field( $raw_value ) );
                    } elseif ( 'url' === $type ) {
                        update_term_meta( $term_id, $key, esc_url_raw( $raw_value ) );
                    } else {
                        update_term_meta( $term_id, $key, sanitize_text_field( $raw_value ) );
                    }
                }
            };

            add_action( "created_{$taxonomy}", $save_callback );
            add_action( "edited_{$taxonomy}", $save_callback );
        }
    }
} );
