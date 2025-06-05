<?php
/*
Plugin Name: Klaim Promo Table
Description: Menampilkan klaim promo dengan filter dan ekspor ke Excel.
Version: 1.0
*/

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class RSP_Claims_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct(array(
            'singular' => __('Klaim Promo', 'referral-store-promo'),
            'plural'   => __('Klaim Promo', 'referral-store-promo'),
            'ajax'     => false
        ));
    }

    public function get_columns() {
        return array(
            'kode_store'     => __('Kode Store', 'referral-store-promo'),
            'nama_store'     => __('Nama Store', 'referral-store-promo'),
            'no_whatsapp'    => __('No. WhatsApp', 'referral-store-promo'),
            'kode_promo'     => __('Kode Promo', 'referral-store-promo'),
            'tanggal_klaim'  => __('Waktu Klaim', 'referral-store-promo'),
        );
    }

    public function get_sortable_columns() {
        return array(
            'tanggal_klaim' => array('tanggal_klaim', true),
            'kode_store'    => array('kode_store', false),
            'nama_store'    => array('nama_store', false),
            'kode_promo'    => array('kode_promo', false),
        );
    }

    public function extra_tablenav($which) {
        if ($which !== 'top') return;

        global $wpdb;
        $table_name = $wpdb->prefix . 'klaim_promo';
        $promo_options = $wpdb->get_col("SELECT DISTINCT kode_promo FROM {$table_name} WHERE kode_promo IS NOT NULL AND kode_promo != '' ORDER BY kode_promo ASC");
        $store_options = $wpdb->get_col("SELECT DISTINCT nama_store FROM {$table_name} WHERE nama_store IS NOT NULL AND nama_store != '' ORDER BY nama_store ASC");

        $selected_promo = isset($_GET['filter_kode_promo']) ? sanitize_text_field($_GET['filter_kode_promo']) : '';
        $selected_store = isset($_GET['filter_nama_store']) ? sanitize_text_field($_GET['filter_nama_store']) : '';

        $export_url_params = array(
            'page' => $_REQUEST['page'],
            'export_excel' => 'true',
        );
        if (!empty($selected_promo)) $export_url_params['filter_kode_promo'] = $selected_promo;
        if (!empty($selected_store)) $export_url_params['filter_nama_store'] = $selected_store;
        if (!empty($_REQUEST['s'])) $export_url_params['s'] = $_REQUEST['s'];
        if (!empty($_REQUEST['orderby'])) $export_url_params['orderby'] = $_REQUEST['orderby'];
        if (!empty($_REQUEST['order'])) $export_url_params['order'] = $_REQUEST['order'];

        $export_url = add_query_arg($export_url_params, admin_url('admin.php'));

        echo '<div class="alignleft actions">';
        if (!empty($promo_options)) {
            echo '<select name="filter_kode_promo">';
            echo '<option value="">Semua Kode Promo</option>';
            foreach ($promo_options as $promo) {
                printf('<option value="%s"%s>%s</option>', esc_attr($promo), selected($selected_promo, $promo, false), esc_html($promo));
            }
            echo '</select>';
        }

        if (!empty($store_options)) {
            echo '<select name="filter_nama_store">';
            echo '<option value="">Semua Nama Store</option>';
            foreach ($store_options as $store) {
                printf('<option value="%s"%s>%s</option>', esc_attr($store), selected($selected_store, $store, false), esc_html($store));
            }
            echo '</select>';
        }

        submit_button(__('Filter'), 'secondary', 'filter_action', false);
        echo '&nbsp;<a href="' . esc_url($export_url) . '" class="button">Export ke Excel</a>';
        echo '</div>';
    }

    public function prepare_items() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'klaim_promo';
        $per_page = 20;
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $where = array();
        $sql_params = array();
        $sql_query = "SELECT * FROM {$table_name}";
        $count_query = "SELECT COUNT(id) FROM {$table_name}";

        $search_term = isset($_REQUEST['s']) ? sanitize_text_field(wp_unslash($_REQUEST['s'])) : '';
        if (!empty($search_term)) {
            $like = '%' . $wpdb->esc_like($search_term) . '%';
            $where[] = "(kode_store LIKE %s OR nama_store LIKE %s OR no_whatsapp LIKE %s OR kode_promo LIKE %s)";
            array_push($sql_params, $like, $like, $like, $like);
        }

        $selected_promo = isset($_GET['filter_kode_promo']) ? sanitize_text_field($_GET['filter_kode_promo']) : '';
        $selected_store = isset($_GET['filter_nama_store']) ? sanitize_text_field($_GET['filter_nama_store']) : '';
        if (!empty($selected_promo)) {
            $where[] = "kode_promo = %s";
            $sql_params[] = $selected_promo;
        }
        if (!empty($selected_store)) {
            $where[] = "nama_store = %s";
            $sql_params[] = $selected_store;
        }

        if (!empty($where)) {
            $sql_query .= ' WHERE ' . implode(' AND ', $where);
            $count_query .= ' WHERE ' . implode(' AND ', $where);
        }

        $orderby = (!empty($_REQUEST['orderby']) && array_key_exists($_REQUEST['orderby'], $sortable)) ? sanitize_sql_orderby($_REQUEST['orderby']) : 'tanggal_klaim';
        $order   = (!empty($_REQUEST['order']) && in_array(strtoupper($_REQUEST['order']), ['ASC', 'DESC'])) ? strtoupper($_REQUEST['order']) : 'DESC';
        $sql_query .= " ORDER BY $orderby $order";

        // Export Excel
        // Export CSV
if (isset($_GET['export_excel']) && $_GET['export_excel'] === 'true') {
    $results = $wpdb->get_results($wpdb->prepare($sql_query, $sql_params), ARRAY_A);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=klaim_promo_' . date('Ymd_His') . '.csv');
    header("Pragma: no-cache");
    header("Expires: 0");

    $output = fopen('php://output', 'w');
    fputcsv($output, array_values($this->get_columns()));

    foreach ($results as $row) {
        $line = [];
        foreach (array_keys($this->get_columns()) as $col) {
            $line[] = isset($row[$col]) ? $row[$col] : '';
        }
        fputcsv($output, $line);
    }

    fclose($output);
    exit;
}

        $total_items = $wpdb->get_var($wpdb->prepare($count_query, $sql_params));
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($paged - 1) * $per_page;
        $sql_query .= " LIMIT %d OFFSET %d";
        array_push($sql_params, $per_page, $offset);

        $this->items = $wpdb->get_results($wpdb->prepare($sql_query, $sql_params), ARRAY_A);
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ));
    }

    public function column_default($item, $column_name) {
        return $item[$column_name] ?? '';
    }
}

// Tambahkan halaman menu admin
// zzadd_action('admin_menu', function () {
//     add_menu_page(
//         'Klaim Promo',
//         'Klaim Promo',
//         'manage_options',
//         'klaim-promo',
//         function () {
//             echo '<div class="wrap"><h1>Klaim Promo</h1>';
//             $list_table = new RSP_Claims_List_Table();
//             $list_table->prepare_items();
//             echo '<form method="get">';
//             echo '<input type="hidden" name="page" value="klaim-promo" />';
//             $list_table->search_box(__('Cari', 'referral-store-promo'), 'klaim-promo');
//             $list_table->display();
//             echo '</form>';
//             echo '</div>';
//         },
//         'dashicons-clipboard',
//         30
//     );
// });