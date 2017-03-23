<?php

class catv_database
{
    public static function assign_missing_categories()
    {
        $args = array(
            'numberposts' => -1,
            'post_type' => 'fact',
            'post_status' => 'any'
        );
        $facts = get_posts($args);
        foreach ($facts as $fact) {
            if (!get_the_terms($fact->ID, 'fm_fact_categories')) {
                wp_set_object_terms($fact->ID, 'Uncategorized', 'fm_fact_categories');
            }
        }
    }

    public static function assign_new_post_category($ID, $post)
    {
        if (!get_the_terms($ID, 'fm_fact_categories')) {
            wp_set_object_terms($ID, 'Uncategorized', 'fm_fact_categories');
        }
    }

    public static function get_all_fact_posts()
    {
        $args = array(
            'numberposts' => -1,
            'post_type' => 'fact',
            'post_status' => 'any'
        );
        $facts = get_posts($args);
        return self::assign_fact_map_data($facts);
    }

    public static function get_filtered_facts($regions, $categories)
    {
        $posts = get_posts(array(
            'post_type' => 'fact',
            'numberposts' => -1,
            'tax_query' => array(
                'relation' => 'AND',
                array(
                    'taxonomy' => 'region',
                    'field' => 'term_id',
                    'terms' => $regions
                ),
                array(
                    'taxonomy' => 'fm_fact_categories',
                    'field' => 'term_id',
                    'terms' => $categories
                ),
            ),
            'meta_query' => array(
                array(
                    'key' => 'fm_fact_lat',
                    'compare' => 'EXISTS'
                ),
                array(
                    'key' => 'fm_fact_lng',
                    'compare' => 'EXISTS'
                )
            ),
            'post_status' => 'publish'
        ));
        $posts = catv_database::assign_fact_map_data($posts);
        return $posts;
    }

    public static function assign_fact_map_data($facts)
    {
        foreach ($facts as $fact) {
            $fact->thumbnail = get_the_post_thumbnail_url($fact->ID, 'medium');
            if (!$fact->thumbnail) {
                $fact->thumbnail = '';
            }
            $fact->lat = get_post_meta($fact->ID, 'fm_fact_lat', true);
            $fact->lng = get_post_meta($fact->ID, 'fm_fact_lng', true);
        }
        return $facts;
    }

    public static function get_visible_fact_posts()
    {
        $args = array(
            'numberposts' => -1,
            'post_type' => 'fact',
            "meta_key" => 'fm_fact_lng',
            'post_status' => 'publish'
        );
        $facts = get_posts($args);
        $facts = catv_database::assign_fact_map_data($facts);
        return $facts;
    }

    public static function get_fact($id)
    {
        $fact = get_post($id);
        $fact->lat = get_post_meta($fact->ID, 'fm_fact_lat', true);
        $fact->lng = get_post_meta($fact->ID, 'fm_fact_lng', true);
        return $fact;
    }

    public static function update_fact_location($fact_id, $fact_lat, $fact_lng)
    {
        $lat_update = update_post_meta($fact_id, 'fm_fact_lat', $fact_lat);
        $lng_update = update_post_meta($fact_id, 'fm_fact_lng', $fact_lng);
        return ($lat_update && $lng_update);
    }

    public static function unset_fact_location($fact_id)
    {
        $lat_delete = delete_post_meta($fact_id, 'fm_fact_lat');
        $lng_delete = delete_post_meta($fact_id, 'fm_fact_lng');
        return ($lat_delete && $lng_delete);
    }
}