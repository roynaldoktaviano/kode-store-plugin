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
        // Pastikan kunci ini cocok dengan nama kolom di tabel database Anda
        // atau alias yang Anda gunakan dalam query SELECT.
        return array(
            // 'cb'             => '<input type="checkbox" />', // Uncomment jika Anda butuh bulk actions
            'kode_store'     => __('Kode Store', 'referral-store-promo'),
            'nama_store'     => __('Nama Store', 'referral-store-promo'),
            'no_whatsapp'    => __('No. WhatsApp', 'referral-store-promo'),
            'kode_promo'     => __('Kode Promo', 'referral-store-promo'),
            'tanggal_klaim'  => __('Waktu Klaim', 'referral-store-promo'),
        );
    }

    public function get_sortable_columns() {
        return array(
            'tanggal_klaim' => array('tanggal_klaim', true), // true berarti default sort descending
            'kode_store'    => array('kode_store', false),
            'nama_store'    => array('nama_store', false),
            'kode_promo'    => array('kode_promo', false),
        );
    }

    public function extra_tablenav($which) {
        if ($which !== 'top') {
            return;
        }

        global $wpdb;
        // PASTIKAN NAMA TABEL INI BENAR
        $table_name = $wpdb->prefix . 'klaim_promo'; // Atau 'rsp_promo_claims' atau nama tabel Anda yang sebenarnya

        // Ambil opsi filter dari database
        // Pastikan nama kolom 'kode_promo' dan 'nama_store' ada di tabel Anda
        $promo_options = $wpdb->get_col("SELECT DISTINCT kode_promo FROM {$table_name} WHERE kode_promo IS NOT NULL AND kode_promo != '' ORDER BY kode_promo ASC");
        $store_options = $wpdb->get_col("SELECT DISTINCT nama_store FROM {$table_name} WHERE nama_store IS NOT NULL AND nama_store != '' ORDER BY nama_store ASC");

        $selected_promo = isset($_GET['filter_kode_promo']) ? sanitize_text_field($_GET['filter_kode_promo']) : '';
        $selected_store = isset($_GET['filter_nama_store']) ? sanitize_text_field($_GET['filter_nama_store']) : '';

        echo '<div class="alignleft actions">';

        // Filter Kode Promo
        if (!empty($promo_options)) {
            echo '<select name="filter_kode_promo" id="filter_kode_promo">';
            echo '<option value="">Semua Kode Promo</option>';
            foreach ($promo_options as $promo) {
                printf('<option value="%s"%s>%s</option>', esc_attr($promo), selected($selected_promo, $promo, false), esc_html($promo));
            }
            echo '</select>';
        }

        // Filter Nama Store
        if (!empty($store_options)) {
            echo '<select name="filter_nama_store" id="filter_nama_store">';
            echo '<option value="">Semua Nama Store</option>';
            foreach ($store_options as $store) {
                printf('<option value="%s"%s>%s</option>', esc_attr($store), selected($selected_store, $store, false), esc_html($store));
            }
            echo '</select>';
        }
        
        // Tombol Filter
        submit_button(__('Filter'), 'secondary', 'filter_action', false, array('id' => 'post-query-submit'));
        
        // Tombol Export
        // Membuat URL untuk export dengan mempertahankan filter yang ada
        $export_url_params = array(
            'page' => $_REQUEST['page'], // Halaman admin saat ini
            'export_excel' => 'true' // Parameter untuk trigger export
        );
        if (!empty($selected_promo)) $export_url_params['filter_kode_promo'] = $selected_promo;
        if (!empty($selected_store)) $export_url_params['filter_nama_store'] = $selected_store;
        if (!empty($_REQUEST['s'])) $export_url_params['s'] = $_REQUEST['s']; // Pertahankan search term
        if (!empty($_REQUEST['orderby'])) $export_url_params['orderby'] = $_REQUEST['orderby'];
        if (!empty($_REQUEST['order'])) $export_url_params['order'] = $_REQUEST['order'];

        $export_url = add_query_arg($export_url_params, admin_url('admin.php')); // Atau edit.php?post_type=... jika ini submenu
        
        echo '&nbsp;';
        // Menggunakan link biasa untuk export agar tidak submit form filter utama
        echo '<a href="' . esc_url($export_url) . '" class="button">' . __('Export ke Excel', 'referral-store-promo') . '</a>';

        echo '</div>';
    }

    public function prepare_items() {
        global $wpdb;
        // PASTIKAN NAMA TABEL INI BENAR
        $table_name = 'wp_klaim_promo'; // Atau 'rsp_promo_claims' atau nama tabel Anda yang sebenarnya
        $per_page = 20;

        // Definisikan kolom
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        // Inisialisasi query dasar dan array untuk klausa WHERE
        // Jika nama kolom di DB berbeda, gunakan alias di sini, contoh:
        // $sql_select_fields = "id, kode_store_input AS kode_store, nama_store_hasil_api AS nama_store, telepon_customer AS no_whatsapp, kode_promo_digunakan AS kode_promo, waktu_klaim AS tanggal_klaim";
        // $sql_query = "SELECT {$sql_select_fields} FROM {$table_name}";
        // Untuk sekarang, kita asumsikan nama kolom DB cocok dengan get_columns()
        $sql_query = "SELECT * FROM {$table_name}";
        $count_query = "SELECT COUNT(id) FROM {$table_name}";
        $where = array();
        $sql_params = array(); // Untuk $wpdb->prepare

        // 1. Terapkan Filter Pencarian
        $search_term = isset($_REQUEST['s']) ? sanitize_text_field(wp_unslash($_REQUEST['s'])) : '';
        if (!empty($search_term)) {
            $like_term = '%' . $wpdb->esc_like($search_term) . '%';
            $search_conditions = array();
            // Pastikan nama kolom ini ada di tabel Anda
            $search_conditions[] = "no_whatsapp LIKE %s";
            $sql_params[] = $like_term;
            $search_conditions[] = "kode_store LIKE %s";
            $sql_params[] = $like_term;
            $search_conditions[] = "nama_store LIKE %s";
            $sql_params[] = $like_term;
            $search_conditions[] = "kode_promo LIKE %s";
            $sql_params[] = $like_term;
            
            $where[] = "(" . implode(" OR ", $search_conditions) . ")";
        }

        // 2. Terapkan Filter Dropdown
        if (!empty($_GET['filter_kode_promo'])) {
            $where[] = "kode_promo = %s";
            $sql_params[] = $selected_promo;
        }
        if (!empty($_GET['filter_nama_store'])) {
            $where[] = "nama_store = %s";
            $sql_params[] = $selected_store;
        }

        // 3. Gabungkan klausa WHERE ke query utama
        if (!empty($where)) {
            $sql_query .= " WHERE " . implode(" AND ", $where);
            $count_query .= " WHERE " . implode(" AND ", $where); // Terapkan juga ke query hitung
        }

        // 4. Handle Export ke Excel (sebelum sorting dan pagination untuk data display)
        // Tombol export sekarang menggunakan link, jadi logic ini mungkin perlu dipindah ke hook yang lebih awal
        // atau diperiksa sebelum prepare_items dipanggil.
        // Untuk saat ini, kita biarkan di sini, tapi pastikan URL export benar.
        if (isset($_GET['export_excel']) && $_GET['export_excel'] == 'true') {
            $export_sql_query = $sql_query; // Query sudah difilter

            // Terapkan sorting untuk export jika ada
            $orderby_export = !empty($_GET['orderby']) ? sanitize_sql_orderby($_GET['orderby']) : 'tanggal_klaim';
            $order_export   = !empty($_GET['order']) ? strtoupper(sanitize_key($_GET['order'])) : 'DESC';
            if (array_key_exists($orderby_export, $this->get_sortable_columns()) && in_array($order_export, array('ASC', 'DESC'))) {
                 $export_sql_query .= " ORDER BY {$orderby_export} {$order_export}";
            } else {
                $export_sql_query .= " ORDER BY tanggal_klaim DESC"; // Default sort
            }
            
            $results_export = $wpdb->get_results( $wpdb->prepare($export_sql_query, $sql_params), ARRAY_A );

            header("Content-Type: application/vnd.ms-excel; charset=utf-8");
            header("Content-Disposition: attachment; filename=laporan_klaim_promo_" . date("Ymd_His") . ".xls");
            header("Pragma: no-cache");
            header("Expires: 0");

            $output_excel = "\xEF\xBB\xBF"; // UTF-8 BOM
            
            // Headers
            $column_headers_export = array();
            foreach ($this->get_columns() as $col_key => $col_val) {
                if ($col_key === 'cb') continue; // Skip checkbox
                $column_headers_export[] = $col_val;
            }
            $output_excel .= implode("\t", $column_headers_export) . "\n";

            // Data rows
            if ($results_export) {
                foreach ($results_export as $row) {
                    $row_data = array();
                    foreach ($this->get_columns() as $col_key => $col_val) {
                        if ($col_key === 'cb') continue;
                        $row_data[] = isset($row[$col_key]) ? $row[$col_key] : '';
                    }
                    $output_excel .= implode("\t", $row_data) . "\n";
                }
            }
            echo $output_excel;
            exit;
        }


        // 5. Sorting untuk tampilan tabel
        $orderby = !empty($_GET['orderby']) ? sanitize_sql_orderby($_GET['orderby']) : 'tanggal_klaim';
        $order   = !empty($_GET['order']) ? strtoupper(sanitize_key($_GET['order'])) : 'DESC';
        if (!array_key_exists($orderby, $this->get_sortable_columns()) || !in_array($order, array('ASC', 'DESC'))) {
            $orderby = 'tanggal_klaim'; $order = 'DESC';
        }
        $sql_query .= " ORDER BY {$orderby} {$order}";

        // 6. Pagination untuk tampilan tabel
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;

        // Tambahkan $sql_params ke $count_query jika ada parameter
        if (!empty($sql_params)) {
            $total_items = $wpdb->get_var( $wpdb->prepare($count_query, $sql_params) );
        } else {
            $total_items = $wpdb->get_var( $count_query );
        }
        

        $sql_query .= $wpdb->prepare(" LIMIT %d OFFSET %d", $per_page, $offset);
        
        if (!empty($sql_params)) {
            $this->items = $wpdb->get_results( $wpdb->prepare($sql_query, $sql_params), ARRAY_A );
        } else {
            $this->items = $wpdb->get_results( $sql_query, ARRAY_A );
        }
        

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    }

    public function column_default($item, $column_name) {
        // Pastikan key ada di item sebelum mengaksesnya
        return isset($item[$column_name]) ? esc_html($item[$column_name]) : '';
    }

    // Uncomment jika Anda menggunakan checkbox dan bulk actions
    /*
    public function column_cb($item) {
        // Pastikan 'id' adalah nama kolom primary key Anda
        return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args['singular'], $item['id']);
    }

    public function get_bulk_actions() {
        $actions = array(
            // 'delete' => __('Hapus', 'referral-store-promo')
        );
        return $actions;
    }

    public function process_bulk_action() {
        // Logika untuk memproses bulk action (misalnya, delete)
        // if ('delete' === $this->current_action()) {
            // wp_die('Items deleted (or will be)!');
        // }
    }
    */
}



?>