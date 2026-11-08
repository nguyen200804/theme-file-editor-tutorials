<?php
/**
 * Template Loader
 *
 * Tự động tìm kiếm template file trong các thư mục con tương ứng (page/, single/, archive/)
 * trước khi tìm trong thư mục gốc của theme.
 *
 * @package Theme_File_Editor_Tutorials
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$template_folders = array(
    'frontpage'  => 'page',
    'home'       => 'page',
    'page'       => 'page',
    'single'     => 'single',
    'singular'   => 'single',
    'attachment' => 'single',
    'archive'    => 'archive',
    'category'   => 'archive',
    'tag'        => 'archive',
    'taxonomy'   => 'archive',
    'author'     => 'archive',
    'date'       => 'archive',
);

foreach ( $template_folders as $type => $folder ) {
    add_filter( "{$type}_template_hierarchy", function ( $templates ) use ( $folder ) {
        $result = array();
        foreach ( $templates as $template ) {
            if ( false === strpos( $template, '/' ) ) {
                $result[] = $folder . '/' . $template;
            }
            $result[] = $template;
        }
        return $result;
    } );
}
