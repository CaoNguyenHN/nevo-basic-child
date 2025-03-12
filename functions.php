<?php
/**
 * Nevo child theme functions and definitions.
 *
 * Add your custom PHP in this file.
 * Only edit this file if you have direct access to it on your server (to fix errors if they happen).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
add_action( 'wp_head', 'nevo_load_favicon' );
/**
 * Echo favicon link.
 *
 * @since 1.0.0
 *
 * @return void Return early if WP Site Icon is used.
 */
function nevo_load_favicon() {

	// Defer to the WP site icon functionality if in use.
	if ( function_exists( 'has_site_icon' ) && has_site_icon() )
		return;
	
	// Get the appropriate favicon URL.
	$favicon_url = nevo_get_favicon_url();

	// If a favicon URL is present then use it to echo the full favicon <link> code.
	if ( $favicon_url )
		echo '<link rel="icon" href="' . esc_url( $favicon_url ) . '" />' . "\n";

}

/**
 * Return the appropriate favicon URL.
 *
 * The 'nevo_pre_load_favicon' filter is made available
 * so that the child theme can define its own custom favicon URL.
 * 
 * The value of the final $favicon_url variable uses the
 * 'nevo_favicon_url' filter.
 *
 * @since 1.0.0
 *
 * @return string Path to favicon.
 */
function nevo_get_favicon_url() {
    // Get the paths and URIs of the child and parent themes
    $child_dir_uri  = get_stylesheet_directory_uri();
    $parent_dir_uri = get_template_directory_uri();
    $child_dir_path = get_stylesheet_directory();
    $parent_dir_path = get_template_directory();

    // Check the filter to allow the child theme to automatically assign a favicon
    $pre = apply_filters('nevo_pre_load_favicon', false);

    // If there is a favicon from the filter, use it (ensure it's a valid URL)
    if ($pre !== false) {
        return esc_url(trim($pre));
    }

    // Function to check if favicon exists in a given path and return its URI
    static $cached_favicon = null; // Cache to avoid redundant checks

    if ($cached_favicon === null) {
        $find_favicon = function($dir_path, $dir_uri) {
            foreach (['png', 'ico'] as $ext) {
                $favicon_path = "$dir_path/assets/images/favicon.$ext";
                if (@is_file($favicon_path)) { // Use @ to suppress warnings if path is inaccessible
                    return "$dir_uri/assets/images/favicon.$ext";
                }
            }
            return false;
        };

        // Check child theme, then parent theme for favicon
        $cached_favicon = $find_favicon($child_dir_path, $child_dir_uri) ?: 
                          $find_favicon($parent_dir_path, $parent_dir_uri) ?: '';
    }

    // Allow editing of favicon path via filter
    return esc_url(trim(apply_filters('nevo_favicon_url', $cached_favicon)));
}

function child_theme_enqueue_scripts() {
	
	// Remove woo blocks-style
	wp_dequeue_style( 'wc-blocks-style' );
	// enqueue Main JavaScript
	//wp_enqueue_script('main-js', get_stylesheet_directory_uri() . '/assets/js/main.js', array(), '0.1', true);z
	
}
add_action( 'wp_enqueue_scripts', 'child_theme_enqueue_scripts' );

add_action( 'manage_posts_custom_column', 'nevo_custom_columns_content', 10, 2 );
function nevo_custom_columns_content( $column_name, $post_id ) {
    if ( $column_name === 'featured_image' ) {
		$attrs = array(
			'style' => 'border-radius:6px;'
		);
        if ( has_post_thumbnail( $post_id ) ) {
            echo get_the_post_thumbnail( $post_id, array( 50, 50 ), $attrs );
        } else {
            echo '<span class="dashicons dashicons-admin-media" style="font-size: 2.5rem;"></span>';
        }
    }
}

add_filter( 'manage_post_posts_columns', 'nevo_custom_columns' );
function nevo_custom_columns( $columns ) {
    $new_columns = array();
    $thumbnail_column = array(
        'featured_image' => 'Image',
        'cb' => '<input type="checkbox" />'
    );

    $new_columns = array_merge( $thumbnail_column, $columns );
    return $new_columns;
}
add_action('admin_head', 'nevo_featured_image_column_width');
function nevo_featured_image_column_width() {
	echo '<style type="text/css">.column-featured_image{width:50px;}</style>';
}

// Add WordPress WordCount Column
add_filter('manage_posts_columns', 'nevo_add_wordcount_column');
function nevo_add_wordcount_column($nevo_columns) {
    $nevo_columns['nevo_wordcount'] = 'Word Count';
    return $nevo_columns;
}
// Show WordCount in Admin Panel
add_action('manage_posts_custom_column',  'nevo_show_wordcount');
function nevo_show_wordcount($name) 
{
    global $post;
    switch ($name) 
    {
        case 'nevo_wordcount':
            $nevo_wordcount = nevo_post_wordcount($post->ID);
            echo $nevo_wordcount;
    }
}
// Get individual post word count
function nevo_post_wordcount($post_id) {
    $nevo_post_content = get_post_field( 'post_content', $post_id );
    $nevo_final_wordcount = str_word_count( strip_tags( strip_shortcodes($nevo_post_content) ) );
    return $nevo_final_wordcount;
}
