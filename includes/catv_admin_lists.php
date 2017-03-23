<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class catv_admin_lists extends WP_list_Table
{
    function get_columns()
    {
        $columns = array(
            'post_title' => 'Title',
            'region' => 'Region',
            'post_date' => 'Date modified',
            'post_status' => 'Status'
        );
        return $columns;
    }

    protected function get_views()
    {
        $current = (isset($_GET['fact_type']) ? $_GET['fact_type'] : 'all');
        $status_links = array();
        $class = ($current == 'all' ? ' class="current"' : '');
        $status_links["all"] = "<a href='edit.php?post_type=fact&page=edit-fact-map' {$class}>All</a>";

        $class = ($current == 'wo' ? ' class="current"' : '');
        $status_links["without"] = "<a href='edit.php?post_type=fact&page=edit-fact-map&fact_type=wo' {$class}>Without set location</a>";

        $class = ($current == 'w' ? ' class="current"' : '');
        $status_links["with"] = "<a href='edit.php?post_type=fact&page=edit-fact-map&fact_type=w' {$class}>With set location</a>";
        return $status_links;
    }

    function prepare_items($item_data)
    {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        usort($item_data, array(&$this, 'usort_reorder'));
        $perPage = 20;
        $currentPage = $this->get_pagenum();
        $totalItems = count($item_data);
        if ((($currentPage - 1) * $perPage) > $totalItems) {
            $currentPage = intval($totalItems / $perPage);
        } else {
            $currentPage = $currentPage - 1;
        }
        $this->set_pagination_args(array(
            'total_items' => $totalItems,
            'per_page' => $perPage
        ));
        $item_data = array_slice($item_data, (($currentPage) * $perPage), $perPage);
        $this->items = $item_data;
    }

    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'post_title':
                if (empty($item->post_title)) {
                    $item->post_title = "(no title)";
                }
                return "<a href='options.php?page=edit-fact-location&fact=" . $item->ID . "'>" . $item->post_title . "</a>";
            case 'region':
                return $item->region;
            case 'post_date':
                return $item->post_date;
            case 'post_status':
                if ($item->post_status === 'publish') {
                    return "<span>published</span>";
                } else {
                    return "<span style='color: red'>" . $item->post_status . "</span>";
                }
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'post_title' => array('post_title', false),
            'region' => array('region', false),
            'post_date' => array('post_date', false),
            'post_status' => array('post_status', false)
        );
        return $sortable_columns;
    }

    function usort_reorder($a, $b)
    {
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'post_date';
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'desc';
        if (property_exists($a, $orderby)) {
            $result = strcmp($a->{$orderby}, $b->{$orderby});
            return ($order === 'asc') ? $result : -$result;
        }
    }

    function no_items()
    {
        _e('No posts found in this category.');
    }
}