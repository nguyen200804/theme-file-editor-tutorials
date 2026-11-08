<?php
/**
 * Helper Functions cho Custom Fields
 *
 * Cung cấp các hàm lấy dữ liệu custom fields dễ dùng trong template
 * tương tự ACF: ten_field(), ten_image(), ten_gallery(), v.v.
 *
 * @package Theme_File_Editor_Tutorials
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'ten_field' ) ) {
    /**
     * Lấy giá trị của một field theo key
     *
     * @param string   $key     Tên key của field
     * @param int|null $post_id ID bài viết (mặc định là bài hiện tại)
     * @param mixed    $default Giá trị mặc định nếu field rỗng
     * @return mixed
     */
    function ten_field( $key, $post_id = null, $default = '' ) {
        if ( null === $post_id ) {
            $post_id = get_the_ID();
        }

        if ( ! $post_id ) {
            return $default;
        }

        $value = get_post_meta( $post_id, $key, true );

        if ( '' === $value || false === $value || null === $value ) {
            return $default;
        }

        return $value;
    }
}

if ( ! function_exists( 'ten_image' ) ) {
    /**
     * Lấy thẻ <img> của field ảnh (lưu attachment ID)
     *
     * @param string       $key     Tên key của field
     * @param string|array $size    Kích thước ảnh ('thumbnail', 'medium', 'large', 'full' hoặc mảng kích thước)
     * @param int|null     $post_id ID bài viết
     * @param array        $attr    Thuộc tính bổ sung cho thẻ img (class, alt...)
     * @return string Thẻ HTML <img> hoặc rỗng
     */
    function ten_image( $key, $size = 'large', $post_id = null, $attr = array() ) {
        $attachment_id = ten_field( $key, $post_id );

        if ( ! $attachment_id || ! is_numeric( $attachment_id ) ) {
            return '';
        }

        return wp_get_attachment_image( absint( $attachment_id ), $size, false, $attr );
    }
}

if ( ! function_exists( 'ten_image_url' ) ) {
    /**
     * Lấy đường dẫn URL của field ảnh
     *
     * @param string       $key     Tên key của field
     * @param string|array $size    Kích thước ảnh
     * @param int|null     $post_id ID bài viết
     * @return string URL ảnh hoặc rỗng
     */
    function ten_image_url( $key, $size = 'full', $post_id = null ) {
        $attachment_id = ten_field( $key, $post_id );

        if ( ! $attachment_id || ! is_numeric( $attachment_id ) ) {
            return '';
        }

        $url = wp_get_attachment_image_url( absint( $attachment_id ), $size );
        return $url ? $url : '';
    }
}

if ( ! function_exists( 'ten_gallery' ) ) {
    /**
     * Lấy danh sách ID các ảnh trong gallery
     *
     * @param string   $key     Tên key của field gallery
     * @param int|null $post_id ID bài viết
     * @return array Mảng các attachment ID (int)
     */
    function ten_gallery( $key, $post_id = null ) {
        $val = ten_field( $key, $post_id, array() );

        if ( empty( $val ) ) {
            return array();
        }

        if ( is_string( $val ) ) {
            $ids = explode( ',', $val );
        } elseif ( is_array( $val ) ) {
            $ids = $val;
        } else {
            return array();
        }

        return array_values( array_filter( array_map( 'absint', $ids ) ) );
    }
}

if ( ! function_exists( 'ten_term_field' ) ) {
    /**
     * Lấy giá trị term meta
     *
     * @param string   $key      Tên key của field
     * @param int|null $term_id  ID của term/chuyên mục
     * @param mixed    $default  Giá trị mặc định
     * @return mixed
     */
    function ten_term_field( $key, $term_id = null, $default = '' ) {
        if ( null === $term_id ) {
            $term = get_queried_object();
            if ( $term && isset( $term->term_id ) ) {
                $term_id = $term->term_id;
            }
        }

        if ( ! $term_id ) {
            return $default;
        }

        $value = get_term_meta( $term_id, $key, true );

        if ( '' === $value || false === $value || null === $value ) {
            return $default;
        }

        return $value;
    }
}

if ( ! function_exists( 'ten_term_image' ) ) {
    /**
     * Lấy thẻ <img> của term meta field ảnh
     *
     * @param string       $key     Tên key của field
     * @param string|array $size    Kích thước ảnh
     * @param int|null     $term_id ID của term/chuyên mục
     * @param array        $attr    Thuộc tính bổ sung
     * @return string
     */
    function ten_term_image( $key, $size = 'large', $term_id = null, $attr = array() ) {
        $attachment_id = ten_term_field( $key, $term_id );

        if ( ! $attachment_id || ! is_numeric( $attachment_id ) ) {
            return '';
        }

        return wp_get_attachment_image( absint( $attachment_id ), $size, false, $attr );
    }
}
