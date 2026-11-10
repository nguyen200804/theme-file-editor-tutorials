<?php
/**
 * Theme Functions and Definitions
 * 
 * File cấu hình trung tâm của Theme, tuân thủ đầy đủ các nguyên tắc:
 * 1. Khởi tạo Theme Support & Menus chuẩn WordPress.
 * 2. Nạp CSS & JavaScript qua wp_enqueue_scripts (tự động cập nhật version chống cache).
 * 3. Bóc tách module logic sang thư mục inc/ (Template Loader, Post Types, Custom Fields).
 * 4. Tùy biến truy vấn danh sách bằng hook pre_get_posts (chống lỗi phân trang).
 * 5. Tối ưu bảo mật và làm sạch mã nguồn HTML.
 */

// Ngăn chặn truy cập trực tiếp vào file vì lý do bảo mật
defined( 'ABSPATH' ) || exit;

// Định nghĩa các hằng số cơ bản cho theme
define( 'THEME_DIR', get_template_directory() );
define( 'THEME_URI', get_template_directory_uri() );
define( 'THEME_VERSION', wp_get_theme()->get( 'Version' ) ?: '1.0.0' );

/* ==========================================================================
   1. KHỞI TẠO TÍNH NĂNG THEME (THEME SUPPORT & MENUS)
   ========================================================================== */
function theme_setup() {
    // 1. Tự động thêm thẻ <title> chuẩn SEO vào <head>
    add_theme_support( 'title-tag' );

    // 2. Kích hoạt tính năng Ảnh đại diện (Featured Image)
    add_theme_support( 'post-thumbnails' );

    // 3. Định nghĩa các kích thước ảnh cắt tự động (tùy chọn)
    add_image_size( 'product-card', 400, 400, true ); // Ảnh vuông tỉ lệ 1:1
    add_image_size( 'blog-banner', 800, 450, true );  // Ảnh chữ nhật tỉ lệ 16:9

    // 4. Hỗ trợ Logo tùy biến trong Appearance → Customize
    add_theme_support( 'custom-logo', array(
        'height'      => 80,
        'width'       => 240,
        'flex-height' => true,
        'flex-width'  => true,
    ) );

    // 5. Chuẩn hóa mã HTML5 cho các thẻ mặc định của WordPress
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ) );

    // 6. Đăng ký các vị trí hiển thị Menu điều hướng
    register_nav_menus( array(
        'primary-menu' => 'Menu Chính (Header)',
        'footer-menu'  => 'Menu Chân Trang (Footer)',
        'mobile-menu'  => 'Menu Di Động (Mobile Drawer)',
    ) );
}
add_action( 'after_setup_theme', 'theme_setup' );

/* ==========================================================================
   2. NẠP FILE CSS & JAVASCRIPT (ENQUEUE SCRIPTS & STYLES)
   ========================================================================== */
function theme_enqueue_assets() {
    // 1. Nạp file style.css chính (dùng filemtime tự sinh version chống cache trình duyệt)
    $style_file = THEME_DIR . '/style.css';
    $style_ver  = file_exists( $style_file ) ? filemtime( $style_file ) : THEME_VERSION;
    wp_enqueue_style( 'theme-main-style', get_stylesheet_uri(), array(), $style_ver );

    // 2. Nạp thêm file CSS phụ nếu có tổ chức trong assets/css/
    if ( file_exists( THEME_DIR . '/assets/css/main.css' ) ) {
        wp_enqueue_style( 'theme-custom-style', THEME_URI . '/assets/css/main.css', array(), filemtime( THEME_DIR . '/assets/css/main.css' ) );
    }

    // 3. Nạp file JavaScript chính (in_footer = true để không chặn render trang)
    $script_file = THEME_DIR . '/assets/js/main.js';
    if ( file_exists( $script_file ) ) {
        wp_enqueue_script( 'theme-main-script', THEME_URI . '/assets/js/main.js', array(), filemtime( $script_file ), true );
    }

    // 4. Nạp script hỗ trợ trả lời comment lồng nhau (chỉ khi trang bài viết mở comment)
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }

    // 5. Tự động nạp CSS & JS chuyên biệt theo từng trang nếu có file trong assets/
    if ( is_front_page() ) {
        theme_auto_enqueue_asset( 'front-page' );
    } elseif ( is_page() ) {
        $slug = get_post_field( 'post_name', get_queried_object_id() );
        theme_auto_enqueue_asset( 'page' );
        if ( $slug ) {
            theme_auto_enqueue_asset( "page-{$slug}" );
        }
    } elseif ( is_singular() ) {
        $post_type = get_post_type();
        theme_auto_enqueue_asset( 'single' );
        if ( $post_type ) {
            theme_auto_enqueue_asset( "single-{$post_type}" );
        }
    } elseif ( is_post_type_archive() ) {
        $post_type = get_query_var( 'post_type' );
        theme_auto_enqueue_asset( 'archive' );
        if ( $post_type ) {
            theme_auto_enqueue_asset( "archive-{$post_type}" );
        }
    } elseif ( is_category() ) {
        theme_auto_enqueue_asset( 'category' );
    } elseif ( is_tag() ) {
        theme_auto_enqueue_asset( 'tag' );
    } elseif ( is_tax() ) {
        $taxonomy = get_query_var( 'taxonomy' );
        theme_auto_enqueue_asset( 'taxonomy' );
        if ( $taxonomy ) {
            theme_auto_enqueue_asset( "taxonomy-{$taxonomy}" );
        }
    } elseif ( is_search() ) {
        theme_auto_enqueue_asset( 'search' );
    } elseif ( is_404() ) {
        theme_auto_enqueue_asset( '404' );
    }
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_assets' );

/**
 * Hàm trợ giúp: Tự kiểm tra nếu file có trong assets/css/ hoặc assets/js/ thì mới nạp
 */
function theme_auto_enqueue_asset( $name ) {
    // Tự nạp CSS riêng nếu có
    $css_file = THEME_DIR . "/assets/css/{$name}.css";
    if ( file_exists( $css_file ) ) {
        wp_enqueue_style( "theme-{$name}-style", THEME_URI . "/assets/css/{$name}.css", array( 'theme-main-style' ), filemtime( $css_file ) );
    }

    // Tự nạp JS riêng nếu có
    $js_file = THEME_DIR . "/assets/js/{$name}.js";
    if ( file_exists( $js_file ) ) {
        wp_enqueue_script( "theme-{$name}-script", THEME_URI . "/assets/js/{$name}.js", array( 'theme-main-script' ), filemtime( $js_file ), true );
    }
}

/* ==========================================================================
   3. NẠP CÁC MODULE TỪ THƯ MỤC INC/ (NGUYÊN TẮC BÓC TÁCH MODULE)
   ========================================================================== */
// 1. Bộ nạp template trong thư mục con page/, single/, archive/
if ( file_exists( THEME_DIR . '/inc/template-loader.php' ) ) {
    require_once THEME_DIR . '/inc/template-loader.php';
}

// 2. Đăng ký các Custom Post Type (Sản phẩm, Dự án, Nhân sự...)
if ( file_exists( THEME_DIR . '/inc/custom-post-types.php' ) ) {
    require_once THEME_DIR . '/inc/custom-post-types.php';
}

// 3. Đăng ký các Custom Taxonomy (Loại sản phẩm, Lĩnh vực hoạt động...)
if ( file_exists( THEME_DIR . '/inc/custom-taxonomies.php' ) ) {
    require_once THEME_DIR . '/inc/custom-taxonomies.php';
}

// 4. Trang Cài đặt Theme (Option Page)
if ( file_exists( THEME_DIR . '/inc/custom-option-page.php' ) ) {
    require_once THEME_DIR . '/inc/custom-option-page.php';
}

// 5. Bộ khung Custom Fields thay thế ACF (Meta boxes, Term meta...)
if ( file_exists( THEME_DIR . '/inc/fields/loader.php' ) ) {
    require_once THEME_DIR . '/inc/fields/loader.php';
}

/* ==========================================================================
   4. TỐI ƯU TRUY VẤN ARCHIVE BẰNG PRE_GET_POSTS
   ========================================================================== */
function theme_optimize_archive_queries( $query ) {
    // Chỉ can thiệp ngoài giao diện frontend và là truy vấn chính (Main Query)
    if ( ! is_admin() && $query->is_main_query() ) {
        // Tùy biến số lượng kết quả trên trang Tìm kiếm
        if ( $query->is_search() ) {
            $query->set( 'posts_per_page', 10 );
        }
    }
}
add_action( 'pre_get_posts', 'theme_optimize_archive_queries' );

/* ==========================================================================
   5. BẢO MẬT & TINH CHỈNH TIỆN ÍCH
   ========================================================================== */
// 1. Xóa bỏ tiền tố mặc định của the_archive_title() (bỏ chữ "Chuyên mục:", "Thẻ:")
add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );

// 2. Tùy biến độ dài trích dẫn ngắn (Excerpt)
add_filter( 'excerpt_length', function() {
    return 25; // Giới hạn 25 từ
} );

add_filter( 'excerpt_more', function() {
    return '...';
} );

// 3. Ẩn phiên bản WordPress khỏi thẻ <head> nhằm tăng cường bảo mật
remove_action( 'wp_head', 'wp_generator' );

// 4. Cho phép upload file SVG an toàn trong Admin
add_filter( 'upload_mimes', function( $mimes ) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
} );

add_filter( 'use_block_editor_for_post_type', '__return_false', 10 );