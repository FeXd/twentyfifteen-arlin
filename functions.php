<?php
function twentyfifteen_child_enqueue_styles()
{
    $parent_style = 'twentyfifteen-style';

    wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css');
    wp_enqueue_style(
        'twentyfifteen-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array($parent_style),
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'twentyfifteen_child_enqueue_styles');

/**
 * Generates output for the ap_content_arlin shortcode
 * This is based on the ap_content shortcode from https://github.com/pfefferle/wordpress-activitypub
 * The only difference is that some tags like <img>, <figure>, and <figcaption> are stripped out
 *
 * @param array  $atts      shortcode attributes
 * @param string $content   shortcode content
 * @param string $tag       shortcode tag name
 *
 * @return string
 */
function ap_content_arlin_shortcode($atts, $content, $tag)
{
    $post = get_post();

    if (!$post || \post_password_required($post)) {
        return '';
    }

    $atts = shortcode_atts(
        array('apply_filters' => 'yes'),
        $atts,
        $tag
    );

    $content = \get_post_field('post_content', $post);

    if ('yes' === $atts['apply_filters']) {
        $content = \apply_filters('the_content', $content);
    } else {
        $content = do_blocks($content);
        $content = wptexturize($content);
        $content = wp_filter_content_tags($content);
    }

    // replace script and style elements
    $content = \preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $content);
    // replace img elements
    $content = preg_replace('/<img[^>]*>/i', '', $content);
    // replace figure elements
    $content = preg_replace('/<figure[^>]*>.*?<\/figure>/is', '', $content);
    // replace figcaption elements
    $content = preg_replace('/<figcaption[^>]*>.*?<\/figcaption>/is', '', $content);

    $content = \trim(\preg_replace('/[\n\r\t]/', '', $content));

    return $content;
}

add_shortcode('ap_content_arlin', 'ap_content_arlin_shortcode');
