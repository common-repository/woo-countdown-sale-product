<?php 
/*
 * Plugin Name: C4D Woocommerce Countdown Sale
 * Plugin URI: https://coffee4dev.com
 * Description: Create countdown clock for sale product
 * Author: Coffee4Dev
 * Author URI: http://coffee4dev.com
 * Version: 2.0.8
 * Text Domain: c4d-wcd
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define('C4D_WCD_PLUGIN_URI', plugins_url('', __FILE__));

add_action( 'wp_enqueue_scripts', 'c4d_wcd_safely_add_stylesheet_to_frontsite');
add_action( 'woocommerce_single_product_summary', 'c4d_wcd_single_countdown', 40 );
add_action( 'woocommerce_before_shop_loop_item_title', 'c4d_wcd_loop_countdown', 20 );
add_action( 'c4d-plugin-manager-section', 'c4d_wcd_section_options');
add_filter( 'plugin_row_meta', 'c4d_wcd_plugin_row_meta', 10, 2 );

add_shortcode( 'c4d_wcd_clock', 'c4d_wcd_clock');
add_shortcode( 'c4d_wcd_template', 'c4d_wcd_template');
add_shortcode( 'c4d_wcd_countdown', 'c4d_wcd_countdown');


function c4d_wcd_safely_add_stylesheet_to_frontsite( $page ) {
    if(!defined('C4DPLUGINMANAGER_OFF_JS_CSS')) {
        wp_enqueue_style( 'c4d-wcd-frontsite-style', C4D_WCD_PLUGIN_URI.'/assets/default.css' );
    }
    wp_enqueue_script( 'c4d-wcd-frontsite-plugin-js', C4D_WCD_PLUGIN_URI.'/jquery.plugin.min.js', array( 'jquery' ), false, true ); 
    wp_enqueue_script( 'c4d-wcd-frontsite-countdown-js', C4D_WCD_PLUGIN_URI.'/jquery.countdown.min.js', array( 'jquery' ), false, true ); 
}

function c4d_wcd_plugin_row_meta( $links, $file ) {
    if ( strpos( $file, basename(__FILE__) ) !== false ) {
        $new_links = array(
            'visit' => '<a href="http://coffee4dev.com">Visit Plugin Site</<a>',
            'premium' => '<a href="http://coffee4dev.com">Premium Support</<a>'
        );
        $links = array_merge( $links, $new_links );
    }
    return $links;
}

function c4d_wcd_single_countdown() {
    global $product, $c4d_plugin_manager;
    if(isset($c4d_plugin_manager['c4d-wcd-single-show']) && $c4d_plugin_manager['c4d-wcd-single-show'] == 1) {
        echo do_shortcode('[c4d_wcd_clock id="'.$product->get_id().'"]');
    }
}

function c4d_wcd_loop_countdown() {
    global $product, $c4d_plugin_manager;
    if(isset($c4d_plugin_manager['c4d-wcd-loop-show']) && $c4d_plugin_manager['c4d-wcd-loop-show'] == 1) {
        echo do_shortcode('[c4d_wcd_clock id="'.$product->get_id().'"]');
    }
}

function c4d_wcd_template($atts) {
    $query_args = array(
        'numberpost'        => 1,
        'posts_per_page'    => 1,
        'no_found_rows'     => 1,
        'post_status'       => 'publish',
        'post_type'         => 'product',
        'meta_query'        => WC()->query->get_meta_query(),
        'post__in'          => isset($atts['id']) ? array($atts['id']) : wc_get_product_ids_on_sale(),
        'orderby'           => 'date',
        'order'             => 'desc'
    );

    $query = new WP_Query( $query_args );
    ob_start();
    while ( $query->have_posts() ):
        $product = $query->the_post();
        $file = get_template_directory(). '/c4d-woo-countdown/templates/default.php';
        if (file_exists($file)) {
            require $file;
        } else {
            require dirname(__FILE__). '/templates/default.php';
        }
    endwhile; 
    woocommerce_reset_loop();
    wp_reset_postdata();
    $html = ob_get_contents();
    ob_end_clean();
    return $html;
}

function c4d_wcd_clock($params) {
    global $c4d_plugin_manager;
    if (!isset($params['id'])) return;

    $product = wc_get_product($params['id']);

    if ($product && $product->is_on_sale()) {
        $params['to'] = strtotime(' +1 day');
        $current = time();
        $to = get_post_meta($params['id'], '_sale_price_dates_to', true);
        $from = get_post_meta($params['id'], '_sale_price_dates_from', true);
        
        if ($from && $current < $from) return '';

        if (isset($c4d_plugin_manager['c4d-wcd-default-time']) && $c4d_plugin_manager['c4d-wcd-default-time'] != '') {
            $params['to'] = $current + ($c4d_plugin_manager['c4d-wcd-default-time'] * 24 * 60 * 60);
        }
        if ($to && $to != '') {
            $params['to'] = $to;
        }
        
        return c4d_wcd_countdown($params);
    }
}

function c4d_wcd_countdown($params) {
    global $c4d_plugin_manager;
    $params = array_merge(array('show_before' => 1, 'show_after' => 1), $params);
    $id = 'c4d-wcd-'.uniqid();
    $html = '';
    if ($params['show_before'] == 1) {
        $html = isset($c4d_plugin_manager['c4d-wcd-single-before-text']) && $c4d_plugin_manager['c4d-wcd-single-before-text'] != '' ? '<div class="c4d-wcd-single-before-text">' .$c4d_plugin_manager['c4d-wcd-single-before-text']. '</div>' : '';
    }
    
    $html .= '<div class="c4d-wcd-wrap" id="'.$id.'"><div class="c4d-wcd__clock"></div></div>
        <script>
        (function($){
            $(document).ready(function(){
                $("#'.$id.' > .c4d-wcd__clock").countdown({
                    until: new Date("'.date("Y-m-d H:i:s", $params['to']).'"),
                    format: "dhMS",
                    padZeroes: true
                });
            });
        })(jQuery);
        </script>';
    if ($params['show_after'] == 1) {
        $html .= isset($c4d_plugin_manager['c4d-wcd-single-after-text']) && $c4d_plugin_manager['c4d-wcd-single-after-text'] != '' ? '<div class="c4d-wcd-single-after-text">' .$c4d_plugin_manager['c4d-wcd-single-after-text']. '</div>' : '';    
    }
    
    return $html;
}

function c4d_wcd_section_options(){
    $opt_name = 'c4d_plugin_manager';
    Redux::setSection( $opt_name, array(
        'title'            => esc_html__( 'Count Down', 'c4d-wcd' ),
        'id'               => 'c4d-wcd',
        'desc'             => '',
        'customizer_width' => '400px',
        'icon'             => 'el el-home',
        'fields'           => array(
            array(
                'id'       => 'c4d-wcd-default-time',
                'type'     => 'text',
                'title'    => esc_html__('Default Time', 'c4d-wcd'),
                'subtitle'    => esc_html__('Set default count down day for sale product without sale time. ', 'c4d-wcd'),
                'default'  => '1'
            ),
            array(
                'id'       => 'c4d-wcd-single-show',
                'type'     => 'button_set',
                'title'    => esc_html__('Show in Single Page', 'c4d-wcd'),
                'options' => array(
                    '0' => esc_html__('No', 'c4d-wcd'),
                    '1' => esc_html__('Yes', 'c4d-wcd'),
                ), 
                'default'  => 1
            ),
            array(
                'id'       => 'c4d-wcd-single-before-text',
                'type'     => 'textarea',
                'title'    => esc_html__('Before Single Text', 'c4d-wcd'),
                'default'  => ''
            ),
            array(
                'id'       => 'c4d-wcd-single-after-text',
                'type'     => 'textarea',
                'title'    => esc_html__('After Single Text', 'c4d-wcd'),
                'default'  => ''
            ),
            array(
                'id'       => 'c4d-wcd-loop-show',
                'type'     => 'button_set',
                'title'    => esc_html__('Show in Listing Page', 'c4d-wcd'),
                'options' => array(
                    '0' => esc_html__('No', 'c4d-wcd'),
                    '1' => esc_html__('Yes', 'c4d-wcd'),
                ), 
                'default'  => 0
            ),
        )
    ));
}