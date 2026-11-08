<?php
/**
 * Core Custom Meta Box & Fields Handler
 *
 * Khung khai báo Meta Box, render form fields và lưu dữ liệu.
 *
 * @package Theme_File_Editor_Tutorials
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $ten_registered_meta_boxes;
$ten_registered_meta_boxes = array();

/**
 * Đăng ký một meta box mới
 *
 * @param array $args Cấu hình meta box
 */
function ten_add_meta_box( $args ) {
    global $ten_registered_meta_boxes;

    $defaults = array(
        'id'         => '',
        'title'      => '',
        'post_types' => array( 'post' ),
        'context'    => 'normal',
        'priority'   => 'default',
        'fields'     => array(),
    );

    $box = wp_parse_args( $args, $defaults );
    if ( ! empty( $box['id'] ) ) {
        $ten_registered_meta_boxes[ $box['id'] ] = $box;
    }
}

/**
 * Hook add_meta_boxes
 */
add_action( 'add_meta_boxes', function () {
    global $ten_registered_meta_boxes;

    foreach ( $ten_registered_meta_boxes as $box_id => $box ) {
        foreach ( (array) $box['post_types'] as $post_type ) {
            add_meta_box(
                $box_id,
                $box['title'],
                'ten_render_meta_box_callback',
                $post_type,
                $box['context'],
                $box['priority'],
                array( 'box' => $box )
            );
        }
    }
} );

/**
 * Callback vẽ nội dung meta box
 */
function ten_render_meta_box_callback( $post, $metabox ) {
    $box = $metabox['args']['box'];
    wp_nonce_field( 'ten_meta_box_save_' . $box['id'], 'ten_meta_box_nonce_' . $box['id'] );

    echo '<table class="form-table" role="presentation">';
    foreach ( $box['fields'] as $field ) {
        $value = get_post_meta( $post->ID, $field['key'], true );
        echo '<tr>';
        echo '<th scope="row"><label for="' . esc_attr( $field['key'] ) . '">' . esc_html( $field['label'] ) . '</label></th>';
        echo '<td>';
        ten_render_field_input( $field, $value );
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
}

/**
 * Render từng loại input
 */
function ten_render_field_input( $field, $value, $name_prefix = '' ) {
    $field_key  = $field['key'];
    $input_name = $name_prefix ? "{$name_prefix}[{$field_key}]" : $field_key;
    $field_type = isset( $field['type'] ) ? $field['type'] : 'text';

    switch ( $field_type ) {
        case 'text':
        case 'url':
        case 'email':
        case 'date':
        case 'number':
            echo '<input type="' . esc_attr( $field_type ) . '" name="' . esc_attr( $input_name ) . '" id="' . esc_attr( $field_key ) . '" value="' . esc_attr( $value ) . '" class="regular-text" />';
            break;

        case 'textarea':
            echo '<textarea name="' . esc_attr( $input_name ) . '" id="' . esc_attr( $field_key ) . '" rows="4" class="large-text">' . esc_textarea( $value ) . '</textarea>';
            break;

        case 'checkbox':
            echo '<label><input type="checkbox" name="' . esc_attr( $input_name ) . '" id="' . esc_attr( $field_key ) . '" value="1" ' . checked( '1', $value, false ) . ' /> ' . esc_html( $field['label'] ) . '</label>';
            break;

        case 'select':
            $options = isset( $field['options'] ) && is_array( $field['options'] ) ? $field['options'] : array();
            echo '<select name="' . esc_attr( $input_name ) . '" id="' . esc_attr( $field_key ) . '">';
            foreach ( $options as $val => $text ) {
                echo '<option value="' . esc_attr( $val ) . '" ' . selected( $value, $val, false ) . '>' . esc_html( $text ) . '</option>';
            }
            echo '</select>';
            break;

        case 'image':
            $img_url = $value ? wp_get_attachment_image_url( absint( $value ), 'thumbnail' ) : '';
            echo '<div class="ten-image-field-wrap">';
            echo '<input type="hidden" class="ten-image-id-input" name="' . esc_attr( $input_name ) . '" value="' . esc_attr( $value ) . '" />';
            echo '<div class="ten-image-preview" style="margin-bottom:8px;">';
            if ( $img_url ) {
                echo '<img src="' . esc_url( $img_url ) . '" style="max-width:150px;max-height:150px;border-radius:4px;display:block;" />';
            }
            echo '</div>';
            echo '<button type="button" class="button button-secondary ten-upload-image-button">Chọn ảnh</button> ';
            echo '<button type="button" class="button button-link-delete ten-remove-image-button" style="' . ( $value ? '' : 'display:none;' ) . '">Xóa ảnh</button>';
            echo '</div>';
            break;

        case 'gallery':
            $ids = ! empty( $value ) ? ( is_array( $value ) ? $value : explode( ',', $value ) ) : array();
            $ids_str = implode( ',', array_filter( array_map( 'absint', $ids ) ) );
            echo '<div class="ten-gallery-field-wrap">';
            echo '<input type="hidden" class="ten-gallery-ids-input" name="' . esc_attr( $input_name ) . '" value="' . esc_attr( $ids_str ) . '" />';
            echo '<div class="ten-gallery-preview-list" style="margin-bottom:8px;">';
            foreach ( $ids as $id ) {
                $thumb = wp_get_attachment_image_url( absint( $id ), 'thumbnail' );
                if ( $thumb ) {
                    echo '<div class="ten-gallery-preview-item" data-id="' . esc_attr( $id ) . '" style="display:inline-block;position:relative;margin:0 8px 8px 0;">';
                    echo '<img src="' . esc_url( $thumb ) . '" style="width:75px;height:75px;object-fit:cover;border-radius:4px;border:1px solid #ccd0d4;display:block;" />';
                    echo '<button type="button" class="ten-remove-gallery-item" style="position:absolute;top:-6px;right:-6px;background:#d63638;color:#fff;border:none;border-radius:50%;width:18px;height:18px;line-height:16px;font-size:12px;cursor:pointer;padding:0;">&times;</button>';
                    echo '</div>';
                }
            }
            echo '</div>';
            echo '<button type="button" class="button button-secondary ten-upload-gallery-button">Thêm ảnh gallery</button>';
            echo '</div>';
            break;

        case 'repeater':
            $rows = is_array( $value ) ? $value : array();
            $sub_fields = isset( $field['sub_fields'] ) ? $field['sub_fields'] : array();
            echo '<div class="ten-repeater-wrap">';
            echo '<table class="widefat striped" style="max-width:700px;margin-bottom:8px;">';
            echo '<thead><tr>';
            foreach ( $sub_fields as $sub ) {
                echo '<th>' . esc_html( $sub['label'] ) . '</th>';
            }
            echo '<th style="width:60px;">Xóa</th>';
            echo '</tr></thead>';
            echo '<tbody class="ten-repeater-tbody">';

            foreach ( $rows as $idx => $row_data ) {
                echo '<tr class="ten-repeater-row">';
                foreach ( $sub_fields as $sub ) {
                    $sub_val = isset( $row_data[ $sub['key'] ] ) ? $row_data[ $sub['key'] ] : '';
                    echo '<td>';
                    ten_render_field_input( $sub, $sub_val, "{$input_name}[{$idx}]" );
                    echo '</td>';
                }
                echo '<td><button type="button" class="button button-link-delete ten-repeater-remove-row">&times;</button></td>';
                echo '</tr>';
            }

            echo '</tbody></table>';

            // Template ẩn cho JS thêm dòng mới
            echo '<div class="ten-repeater-template" style="display:none;">';
            echo '<tr class="ten-repeater-row">';
            foreach ( $sub_fields as $sub ) {
                echo '<td>';
                ten_render_field_input( $sub, '', "{$input_name}[{{INDEX}}]" );
                echo '</td>';
            }
            echo '<td><button type="button" class="button button-link-delete ten-repeater-remove-row">&times;</button></td>';
            echo '</tr>';
            echo '</div>';

            echo '<button type="button" class="button button-secondary ten-repeater-add-row">+ Thêm dòng</button>';
            echo '</div>';
            break;
    }
}

/**
 * Hook save_post để lưu meta data
 */
add_action( 'save_post', function ( $post_id ) {
    global $ten_registered_meta_boxes;

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    foreach ( $ten_registered_meta_boxes as $box_id => $box ) {
        $nonce_name = 'ten_meta_box_nonce_' . $box['id'];
        if ( ! isset( $_POST[ $nonce_name ] ) || ! wp_verify_nonce( $_POST[ $nonce_name ], 'ten_meta_box_save_' . $box['id'] ) ) {
            continue;
        }

        foreach ( $box['fields'] as $field ) {
            $key = $field['key'];
            $type = isset( $field['type'] ) ? $field['type'] : 'text';

            if ( 'checkbox' === $type ) {
                $val = isset( $_POST[ $key ] ) ? '1' : '0';
                update_post_meta( $post_id, $key, $val );
                continue;
            }

            if ( ! isset( $_POST[ $key ] ) ) {
                delete_post_meta( $post_id, $key );
                continue;
            }

            $raw_value = $_POST[ $key ];

            if ( 'repeater' === $type && is_array( $raw_value ) ) {
                $cleaned = array();
                foreach ( $raw_value as $row ) {
                    if ( is_array( $row ) ) {
                        $cleaned_row = array();
                        foreach ( $row as $sub_k => $sub_v ) {
                            $cleaned_row[ sanitize_key( $sub_k ) ] = sanitize_text_field( $sub_v );
                        }
                        $cleaned[] = $cleaned_row;
                    }
                }
                update_post_meta( $post_id, $key, array_values( $cleaned ) );
            } elseif ( 'textarea' === $type ) {
                update_post_meta( $post_id, $key, sanitize_textarea_field( $raw_value ) );
            } elseif ( 'email' === $type ) {
                update_post_meta( $post_id, $key, sanitize_email( $raw_value ) );
            } elseif ( 'url' === $type ) {
                update_post_meta( $post_id, $key, esc_url_raw( $raw_value ) );
            } elseif ( 'image' === $type ) {
                update_post_meta( $post_id, $key, absint( $raw_value ) );
            } elseif ( 'gallery' === $type ) {
                $ids = array_filter( array_map( 'absint', explode( ',', $raw_value ) ) );
                update_post_meta( $post_id, $key, $ids );
            } else {
                update_post_meta( $post_id, $key, sanitize_text_field( $raw_value ) );
            }
        }
    }
} );

/**
 * Nạp CSS & JS media trong trang admin
 */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
    if ( in_array( $hook, array( 'post.php', 'post-new.php', 'term.php', 'edit-tags.php' ), true ) ) {
        wp_enqueue_media();
        $js_url = get_stylesheet_directory_uri() . '/assets/js/admin-fields.js';
        wp_enqueue_script( 'ten-admin-fields', $js_url, array( 'jquery' ), '1.0', true );
    }
} );
