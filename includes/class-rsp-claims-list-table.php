<?php
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
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
        
        );
    }

    public function prepare_items() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'klaim_promo';
        $per_page = 20;

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $sql_query = "SELECT * FROM {$table_name}";

        // Search
        $search_term = isset($_REQUEST['s']) ? sanitize_text_field(wp_unslash($_REQUEST['s'])) : '';
        if (!empty($search_term)) {
            $sql_query .= $wpdb->prepare(
                " WHERE (no_whatsapp LIKE %s OR kode_store LIKE %s OR nama_store LIKE %s OR kode_promo LIKE %s)",
                '%' . $wpdb->esc_like($search_term) . '%',
                '%' . $wpdb->esc_like($search_term) . '%',
                '%' . $wpdb->esc_like($search_term) . '%',
                '%' . $wpdb->esc_like($search_term) . '%'
            );
        }

        // Sorting
        $orderby = !empty($_GET['orderby']) ? sanitize_sql_orderby($_GET['orderby']) : 'tanggal_klaim';
        $order   = !empty($_GET['order']) ? strtoupper(sanitize_key($_GET['order'])) : 'DESC';
        if (!array_key_exists($orderby, $this->get_sortable_columns()) || !in_array($order, array('ASC', 'DESC'))) {
            $orderby = 'tanggal_klaim'; $order = 'DESC';
        }
        $sql_query .= " ORDER BY {$orderby} {$order}";

        // Pagination
        $current_page = $this->get_pagenum();
        $total_items_sql = "SELECT COUNT(id) FROM {$table_name}";
        if (!empty($search_term)) {
            $total_items_sql .= $wpdb->prepare(
                " WHERE (no_whatsapp LIKE %s OR kode_store LIKE %s OR nama_store LIKE %s OR kode_promo LIKE %s)",
                '%' . $wpdb->esc_like($search_term) . '%',
                '%' . $wpdb->esc_like($search_term) . '%',
                '%' . $wpdb->esc_like($search_term) . '%',
                '%' . $wpdb->esc_like($search_term) . '%'
            );
        }
        $total_items = $wpdb->get_var($total_items_sql);

        $sql_query .= $wpdb->prepare(" LIMIT %d, %d", (($current_page - 1) * $per_page), $per_page);
        $this->items = $wpdb->get_results($sql_query, ARRAY_A);

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }

    public function column_default($item, $column_name) {
        if (isset($item[$column_name])) {
            return esc_html($item[$column_name]);
        }
        return print_r($item, true);
    }

   
    /*
    public function get_bulk_actions() {
        return array('delete' => __('Hapus', 'referral-store-promo'));
    }

    public function process_bulk_action() {
        if ('delete' === $this->current_action()) {
            $ids = isset($_REQUEST['claim_ids']) ? array_map('intval', $_REQUEST['claim_ids']) : array();
            if (!empty($ids)) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'klaim_promo';
                $id_placeholders = implode(', ', array_fill(0, count($ids), '%d'));
                $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE id IN ($id_placeholders)", $ids));
                // Mungkin tampilkan notice
            }
        }
    }
    */
}