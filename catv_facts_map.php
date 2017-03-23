<?php
/*
Plugin Name: Facts Map
Description: Facts map plugin powered by Google maps API v3
Author: Liu Xing
Version: 1.1
*/

include_once 'includes/catv_database.php';
include_once 'includes/catv_functions.php';
include_once 'includes/catv_admin_lists.php';

register_activation_hook(__FILE__, 'catv_fm_activate');

function catv_fm_activate()
{
    global $wp_version;
    if (version_compare($wp_version, '3.5', '<')) {
        wp_die('This plugin requires WordPress version 3.5 or higher.');
    }

    catv_register_fact_categories();
    $default_fact_categories = array('Castles', 'Chateau', 'Monuments', 'Museum', 'Other Landmarks', 'Traditions', 'Natural Preserves', 'Uncategorized');
    foreach ($default_fact_categories as $category) {
        wp_insert_term($category, 'fm_fact_categories');
    }
    catv_database::assign_missing_categories();
}

function catv_init()
{
    wp_enqueue_script('catv_maps_api');
    wp_enqueue_script('jquery');

    ob_start();
    include 'includes/catv_map_container.php';
    $html = ob_get_contents();
    ob_end_clean();

    catv_map_data_initialize();
    return $html;
}

add_shortcode('facts-map', 'catv_init');

function catv_script_enqueue()
{
    wp_register_script('catv_facts_map', plugins_url(basename(dirname(__FILE__)) . '/js/scripts.js'), '', '', false);
    wp_enqueue_script('catv_facts_map');
    wp_register_script('catv_maps_api', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyDofeZ5deZSJ5tk5s1RzMWWqBqmlGZ1FM4&language=en&region=US&callback=initializeMap', '', '', true);
    wp_register_style('catv_styles', plugins_url(basename(dirname(__FILE__)) . '/css/styles.css', false, '1.0', 'all'));
    wp_enqueue_style('catv_styles');

    wp_enqueue_script('catv-ajax-handle', plugin_dir_url(__FILE__) . '/js/ajax.js', array('jquery'));
    wp_localize_script('catv-ajax-handle', 'fm_ajax_script', array('ajaxurl' => admin_url('admin-ajax.php')));
}

add_action('wp_enqueue_scripts', 'catv_script_enqueue');

function catv_map_data_initialize()
{
    $result = (catv_database::get_visible_fact_posts());
    catv_functions::$catv_facts = catv_functions::assign_fact_regions($result);
    ?>
    <script>
        var marker_data = <?php echo json_encode(catv_functions::$catv_facts) ?>;
        var fact_path = "<?php echo get_post_type_archive_link("fact") ?>";
    </script>
    <?php
}

add_action('admin_menu', 'catv_edit_fact_admin_pages');

function catv_edit_fact_admin_pages()
{
    wp_register_script('catv_admin_facts_map', plugins_url(basename(dirname(__FILE__)) . '/js/admin-scripts.js'), '', '', true);
    wp_enqueue_script('catv_admin_facts_map');
    wp_register_style('catv_admin_styles', plugins_url(basename(dirname(__FILE__)) . '/css/admin-styles.css', false, '1.0', 'all'));
    wp_enqueue_style('catv_admin_styles');

    add_submenu_page('edit.php?post_type=fact', 'Edit Facts Locations', 'Edit Facts Locations', 'edit_posts', 'edit-fact-map', 'catv_functions::render_admin_fact_list');
    add_submenu_page('options.php', 'Edit Fact Location', 'Edit Fact Location', 'edit_posts', 'edit-fact-location', 'catv_functions::render_edit_fact_form');
}

add_filter('parent_file', 'menu_highlight');

function menu_highlight()
{
    $admin_page = get_current_screen();
    if ($admin_page->id == 'admin_page_edit-fact-location') {
        global $submenu_file;
        $submenu_file = 'edit-fact-map';
    }
}

add_action('publish_fact', 'catv_database::assign_new_post_category', 10, 2);
add_action('before_delete_post', 'catv_database::remove_deleted_fact');

add_action('init', 'catv_register_fact_categories');

function catv_register_fact_categories()
{
    register_taxonomy(
        'fm_fact_categories',
        'fact',
        array(
            'hierarchical' => false,
            'label' => __('Fact Categories'),
            'capabilities' => array(
                'assign_terms' => 'edit_posts',
                'edit_terms' => 'edit_posts',
                'delete_terms' => 'edit_posts',
                'manage_terms' => 'edit_posts'
            )
        )
    );
}

function catv_register_session()
{
    if (!session_id())
        session_start();
}

add_action('init', 'catv_register_session');

add_action('admin_post_fm_update_fact', 'catv_functions::fm_form_update_fact');

add_action('wp_ajax_fm_ajax_hook', 'catv_functions::get_filtered_facts');
add_action('wp_ajax_nopriv_fm_ajax_hook', 'catv_functions::get_filtered_facts');