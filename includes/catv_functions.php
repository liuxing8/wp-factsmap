<?php

class catv_functions
{
    public static $catv_facts = array();

    public static function assign_fact_regions($facts)
    {
        foreach ($facts as $fact) {
            $region = wp_get_post_terms($fact->ID, 'region', array("fields" => "names"));
            $fact->region = $region[0];
        }
        return $facts;
    }

    public static function get_regions()
    {
        return get_terms(array(
            'taxonomy' => 'region',
            'hide_empty' => false,
        ));
    }

    public static function get_fact_categories()
    {
        return get_terms(array(
            'taxonomy' => 'fm_fact_categories',
            'hide_empty' => false,
        ));
    }

    public static function filter_facts_by_region($filters)
    {
        $filtered = [];
        foreach ($filters as $filter) {
            foreach (self::$catv_facts as $fact) {
                if (has_term($filter, 'region', $fact->ID)) {
                    array_push($filtered, $fact);
                    unset($fact);
                }
            }
        }
        return $filtered;
    }

    public static function set_filter_regions()
    {
        $regions = catv_functions::get_regions();
        foreach ($regions as $reg) {
            echo "<div class='col-sm-4 filter-checkbox-container'><input id='$reg->term_id' class='checkbox-hidden' type='checkbox' name='region[]' checked value='$reg->term_id' onclick='uncheckSelectAll(this, \"allRegions\")'><label for='$reg->term_id' class='checkbox-inline filter-checkbox'>$reg->name</label></div>";
        }
    }

    public static function set_filter_categories()
    {
        $categories = catv_functions::get_fact_categories();
        foreach ($categories as $cat) {
            echo "<div class='col-sm-4 filter-checkbox-container'><input id='$cat->term_id' class='checkbox-hidden' type='checkbox' name='category[]' checked value='$cat->term_id' onclick='uncheckSelectAll(this, \"allCategories\")'><label for='$cat->term_id' class='checkbox-inline filter-checkbox'>$cat->name</label></div>";
        }
    }

    public static function set_edit_button()
    {
        if (catv_functions::verify_user_capabilities()) {
            echo "<a href='" . admin_url('edit.php?post_type=fact&page=edit-fact-map') . "' class='fm-btn btn btn-default'>Edit Facts Locations</a>";
        }
    }

    public static function admin_flash_message($name = '', $message = '', $class = 'notice notice-success is-dismissible')
    {
        if (!empty($name)) {
            if (!empty($message) && empty($_SESSION[$name])) {
                if (!empty($_SESSION[$name])) {
                    unset($_SESSION[$name]);
                }
                if (!empty($_SESSION[$name . '_class'])) {
                    unset($_SESSION[$name . '_class']);
                }

                $_SESSION[$name] = $message;
                $_SESSION[$name . '_class'] = $class;
            } elseif (!empty($_SESSION[$name]) && empty($message)) {
                $class = !empty($_SESSION[$name . '_class']) ? $_SESSION[$name . '_class'] : 'notice notice-success is-dismissible';
                echo '<div class="' . $class . '" id="msg-flash">' . $_SESSION[$name] . '</div>';
                unset($_SESSION[$name]);
                unset($_SESSION[$name . '_class']);
            }
        }
    }

    public static function render_admin_fact_list($views)
    {
        if (!catv_functions::verify_user_capabilities()) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        echo '<style type="text/css">';
        echo '.wp-list-table .column-post_title { width: 50%; }';
        echo '.wp-list-table .column-region { width: 15%; }';
        echo '.wp-list-table .column-post_modified_gmt { width: 15%; }';
        echo '.wp-list-table .column-post_status { width: 15%; }';
        echo '</style>';
        $facts_arr = catv_database::get_all_fact_posts();
        $facts_arr = self::assign_fact_regions($facts_arr);
        $filtered_facts = array();
        if (!empty($_GET["fact_type"])) {
            if ($_GET["fact_type"] === "wo") {
                foreach ($facts_arr as $fact) {
                    if (empty($fact->lat) || empty($fact->lng)) {
                        $filtered_facts[] = $fact;
                    }
                }
            } elseif ($_GET["fact_type"] === "w") {
                foreach ($facts_arr as $fact) {
                    if (($fact->lat != 0) && ($fact->lng != 0)) {
                        $filtered_facts[] = $fact;
                    }
                }
            }
        } else {
            $filtered_facts = $facts_arr;
        }
        echo '<div class="wrap">
            <h3>Edit Facts Locations</h3>
            <p>By clicking on the name of the post in the lists below, you will be redirected to a form where you can set/edit location of the selected post. You can order the list by clicking on the column name. <br><strong>Only published post will have a marker visible on the map.</strong></p>';
        catv_functions::admin_flash_message('fm_location_update');

        $fact_list = new catv_admin_lists();
        $fact_list->views();
        $fact_list->prepare_items($filtered_facts);
        $fact_list->display();
        echo '</div>';
    }

    public static function render_edit_fact_form()
    {
        if (!empty($_GET['fact'])) {
            $fact = catv_database::get_fact($_GET['fact']);
            if (!empty($fact)) {
                $redirect_url = catv_functions::fm_get_redirect_url(wp_get_referer());
                $_SESSION['fm_admin_redirect'] = $redirect_url;
                ?>
                <h2>Edit Fact Location | <a href='<?php echo $redirect_url; ?>'>Back to List</a>
                </h2>
                <p>In the form below you can set geographic latitude and longitude of the post. You can either fill in
                    the latitude and longitude numbers manually (with <strong>period as a decimal point</strong>, e.g.
                    12.3456789), or you can set the location by placing a marker on the map (left click on map). When
                    using the map below for setting the location, you can use the search field under the map to search
                    for your desired place on the map. That way the location fields will be filled automatically.</p>

                <div id="poststuff">
                    <div id="post-body" class="metabox-holder" style="margin-right: 1rem">
                        <div id="post-body-content" class="admin-container">
                            <div class="admin-container-content">
                                <form name="fm_fact_location" method="POST" action="admin-post.php">
                                    <input type="hidden" name="action" value="fm_update_fact">
                                    <input type="hidden" name="fm_fact_id" value="<?php echo $_GET['fact'] ?>">
                                    <?php
                                    if (empty($fact->post_title)) {
                                        $fact->post_title = "(no title)";
                                    }
                                    echo "<h3>" . $fact->post_title . "</h3><hr>";
                                    //                                    echo the_terms($fact->ID, 'fm_fact_categories', 'Categories: ', ', ', ' ');
                                    wp_nonce_field('save_fm_fact_location', 'save_fm_fact_location_nonce');
                                    ?>
                                    <div class="admin-fm-form-control">
                                        <div class="admin-fm-form-group">
                                            <label for="fm_fact_lat" class="admin-fm-label">Latitude:</label>
                                            <input class="admin-fm-form-input" type="text"
                                                   pattern="[+-]?([0-9]*[.])?[0-9]+" name="fm_fact_lat"
                                                   id="fm_fact_lat" required
                                                <?php
                                                if ($fact->lat != 0) {
                                                    echo "value='" . $fact->lat . "'";
                                                } ?>
                                            />
                                        </div>
                                        <div class="admin-fm-form-group">
                                            <label for="fm_fact_lng" class="admin-fm-label">Longitude:</label>
                                            <input class="admin-fm-form-input" type="text"
                                                   pattern="[+-]?([0-9]*[.])?[0-9]+" name="fm_fact_lng"
                                                   id="fm_fact_lng" required
                                                <?php
                                                if ($fact->lng != 0) {
                                                    echo "value='" . $fact->lng . "'";
                                                } ?>
                                            />
                                        </div>
                                        <div class="admin-fm-form-group">
                                            <?php
                                            submit_button('Save Location', 'admin-fm-form-input primary button-primary', 'submit', false);
                                            submit_button('Unset Location', 'admin-fm-form-input admin-fm-form-delete-btn', 'unset', false);
                                            if (get_post_status($fact->ID) != 'publish') {
                                                echo "<span style='color: red;'><strong>This post is not published.</strong> You can still set the post location, but the marker will not be visible until the post is published.</span>";
                                            } ?>
                                        </div>
                                    </div>
                                    <div id="map" class="admin-fm-map-container">
                                        <script>
                                            var map;
                                            var marker;
                                            function initMap() {
                                                geocoder = new google.maps.Geocoder();
                                                map = new google.maps.Map(document.getElementById('map'), {
                                                    center: {lat: 49.7976857, lng: 15.5096585},
                                                    zoom: 7,
                                                    minZoom: 3,
                                                    mapTypeControl: false,
                                                    panControl: false,
                                                    streetViewControl: false
                                                });
                                                google.maps.event.addListener(map, 'click', function (event) {
                                                    placeMarker(event.latLng);
                                                    setLatLngFields(event.latLng);
                                                });

                                                marker = new google.maps.Marker({
                                                    <?php catv_functions::fm_form_preset_location($fact); ?>
                                                    map: map,
                                                    draggable: false
                                                });
                                            }
                                        </script>
                                        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDofeZ5deZSJ5tk5s1RzMWWqBqmlGZ1FM4&language=en&region=US&callback=initMap"
                                                async defer></script>
                                    </div>
                                </form>
                                <div class="admin-fm-form-control admin-fm-search">
                                    <div class="admin-fm-form-group admin-fm-search-input">
                                        <label for="fact_address" class="admin-fm-label">Search on map:</label>
                                        <input id="fact_address" type="textbox" value="">
                                        <input type="button" class="button button-default" value="Search"
                                               onclick="codeAddress()">
                                    </div>
                                    <div id="search_result" class="admin-fm-form-group admin-fm-search-response"></div>
                                </div>
                                <hr>
                                <h3>Current Post Content |
                                    <?php echo "<small><a href='post.php?post=" . $fact->ID . "&action=edit' target='_blank'>Edit
                                            Original Post</a></small>" ?>
                                </h3>
                                <div class="admin-fm-post-content">
                                    <?php echo apply_filters('the_content', $fact->post_content); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <?php
            } else {
                wp_die("<h2>Invalid URL</h2>");
            }
        } else {
            wp_die("<h2>Invalid URL</h2>");
        }
    }

    public static function fm_get_redirect_url($referer_url)
    {
        $parsed_url = wp_parse_url($referer_url);
        $url_args = wp_parse_args($parsed_url["query"]);
        if (empty($parsed_url["path"]) && ($url_args["page"] != "edit-fact-map")) {
            return "edit.php?post_type=fact&page=edit-fact-map";
        }
        return $referer_url;
    }

    public static function fm_form_preset_location($fact)
    {
        if (!empty($fact->lat) && !empty($fact->lng)) {
            echo "position: {lat: " . $fact->lat . ", lng: " . $fact->lng . "},";
        }
    }

    public static function fm_form_update_fact()
    {
        if (catv_functions::valid_fact_form()) {
            $fact_id = sanitize_text_field($_POST['fm_fact_id']);
            $fact_id = intval($fact_id);
            if (isset($_SESSION['fm_admin_redirect'])) {
                $redirect_url = $_SESSION['fm_admin_redirect'];
                unset($_SESSION['fm_admin_redirect']);
            } else {
                $redirect_url = "edit.php?post_type=fact&page=edit-fact-map";
            }
            if (isset($_POST['submit'])) {
                $fact_lat = sanitize_text_field($_POST['fm_fact_lat']);
                $fact_lat = floatval($fact_lat);
                $fact_lng = sanitize_text_field($_POST['fm_fact_lng']);
                $fact_lng = floatval($fact_lng);
                if ((catv_functions::is_float_value($fact_lat) && $fact_lat != 0) && (catv_functions::is_float_value($fact_lng) && $fact_lng != 0)) {
                    if (catv_database::update_fact_location($fact_id, $fact_lat, $fact_lng) === false) {
                        catv_functions::admin_flash_message('fm_location_update', '<p><strong>A database error occurred.</strong> Fact location was not updated.</p>', 'notice notice-error is-dismissible');
                        wp_redirect($redirect_url);
                        exit;
                    } else {
                        catv_functions::admin_flash_message('fm_location_update', '<p><strong>Fact location was successfully updated.</strong></p>', 'notice notice-success is-dismissible');
                        wp_redirect($redirect_url);
                        exit;
                    }
                } else {
                    catv_functions::admin_flash_message('fm_location_update', '<p><strong>Fact location was not updated!</strong> Wrong format of entered location</p>', 'notice notice-error is-dismissible');
                    wp_redirect($redirect_url);
                    exit;
                }
            } elseif (isset($_POST['unset'])) {
                if (catv_database::unset_fact_location($fact_id) === false) {
                    catv_functions::admin_flash_message('fm_location_update', '<p><strong>A database error occurred.</strong> Fact location was not updated.</p>', 'notice notice-error is-dismissible');
                    wp_redirect($redirect_url);
                    exit;
                } else {
                    catv_functions::admin_flash_message('fm_location_update', '<p><strong>Fact location was successfully updated.</strong></p>', 'notice notice-success is-dismissible');
                    wp_redirect($redirect_url);
                    exit;
                }
            }
        }
    }

    function is_float_value($input_value)
    {
        return is_numeric($input_value) && ('' . (int)$input_value) !== "$input_value";
    }

    public static function valid_fact_form()
    {
        if (!check_admin_referer('save_fm_fact_location', 'save_fm_fact_location_nonce')) {
            return false;
        }

        if (!isset($_POST['save_fm_fact_location_nonce'])) {
            return false;
        }

        if (!wp_verify_nonce($_POST['save_fm_fact_location_nonce'], 'save_fm_fact_location')) {
            return false;
        }

        if (!catv_functions::verify_user_capabilities()) {
            return false;
        }

        if (empty($_POST['fm_fact_id'])) {
            return false;
        }
        return true;
    }

    function verify_user_capabilities()
    {
        return (current_user_can('edit_posts') || current_user_can('edit_others_posts'));
    }

    public static function get_filtered_facts()
    {
        if (!wp_verify_nonce($_POST['fm-ajax-filters-nonce'], 'fm-ajax-filters')) {
            die();
        }

        $regions = $_POST['region'];
        $categories = $_POST['category'];
        $filtered_facts = catv_database::get_filtered_facts($regions, $categories);
        $filtered_facts = catv_functions::assign_fact_regions($filtered_facts);
        echo json_encode($filtered_facts);
        die();
    }
}