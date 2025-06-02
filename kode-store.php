<?php
/**
 * Plugin Name:      Referral Store Promo
 * Description:      Promo List untuk Referal Store
 * Version:          1.2.0 // Versi dinaikkan untuk menandai penambahan fitur
 * Author:           PT Doran Sukses Indonesia
 * Author URI:       https://doran.id/
 * License:          GPL v2 or later
 * License URI:      https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:      referral-store-promo // Pastikan text domain konsisten
 */


if ( ! defined( 'WPINC' ) ) {
    die;
}


define( 'RSP_VERSION', '1.2.0' );
define( 'RSP_PLUGIN_SLUG', 'promo_referral' ); // Ini adalah slug CPT Anda

// Definisikan konstanta path plugin jika belum ada
if ( ! defined( 'RSP_PLUGIN_PATH' ) ) {
    define( 'RSP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'RSP_PLUGIN_URL' ) ) {
    define( 'RSP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

function rsp_enqueue_frontend_styles() {
    // Pastikan RSP_PLUGIN_SLUG digunakan secara konsisten
    if ( is_singular( RSP_PLUGIN_SLUG ) || is_post_type_archive( RSP_PLUGIN_SLUG ) || is_page_template('templates/klaim-promo-konfirmasi.php') /* Tambahkan ini jika ingin style di halaman klaim juga */ ) {
        wp_enqueue_style(
            'rsp-frontend-styles',
            RSP_PLUGIN_URL . 'assets/css/style.css',
            array(),
            RSP_VERSION
        );
        wp_enqueue_script(
            'rsp-frontend-scripts', // Ganti handle 'ydp-dial-pad-scripts' agar lebih relevan jika perlu
            RSP_PLUGIN_URL . 'assets/js/script.js',
            array('jquery'), // Tambahkan jQuery sebagai dependensi jika script.js Anda menggunakannya
            RSP_VERSION,
            true // Muat di footer
        );
    }
}
add_action( 'wp_enqueue_scripts', 'rsp_enqueue_frontend_styles' );



/**
 * Fungsi untuk mendaftarkan Custom Post Type "Promo Referral".
 */
function rsp_register_promo_referral_cpt() {

    $labels = array(
        'name'                  => _x( 'Promo Store', 'Post Type General Name', 'referral-store-promo' ),
        'singular_name'         => _x( 'Promo Store', 'Post Type Singular Name', 'referral-store-promo' ),
        'menu_name'             => __( 'Promo Store', 'referral-store-promo' ),
        'name_admin_bar'        => __( 'Promo Store', 'referral-store-promo' ),
        'archives'              => __( 'Arsip Promo Store', 'referral-store-promo' ),
        'attributes'            => __( 'Atribut Promo Store', 'referral-store-promo' ),
        'parent_item_colon'     => __( 'Induk Promo Store:', 'referral-store-promo' ),
        'all_items'             => __( 'Semua Promo', 'referral-store-promo' ),
        'add_new_item'          => __( 'Tambah Promo Baru', 'referral-store-promo' ),
        'add_new'               => __( 'Tambah Baru', 'referral-store-promo' ),
        'new_item'              => __( 'Promo Baru', 'referral-store-promo' ),
        'edit_item'             => __( 'Edit Promo', 'referral-store-promo' ),
        'update_item'           => __( 'Perbarui Promo', 'referral-store-promo' ),
        'view_item'             => __( 'Lihat Promo', 'referral-store-promo' ),
        'view_items'            => __( 'Lihat Semua Promo', 'referral-store-promo' ),
        'search_items'          => __( 'Cari Promo', 'referral-store-promo' ),
        'not_found'             => __( 'Tidak ditemukan', 'referral-store-promo' ),
        'not_found_in_trash'    => __( 'Tidak ditemukan di Sampah', 'referral-store-promo' ),
        'featured_image'        => __( 'Banner Promo', 'referral-store-promo' ),
        'set_featured_image'    => __( 'Atur Banner Promo', 'referral-store-promo' ),
        'remove_featured_image' => __( 'Hapus Banner Promo', 'referral-store-promo' ),
        'use_featured_image'    => __( 'Gunakan sebagai Banner Promo', 'referral-store-promo' ),
        'insert_into_item'      => __( 'Sisipkan ke dalam promo', 'referral-store-promo' ),
        'uploaded_to_this_item' => __( 'Diunggah ke promo ini', 'referral-store-promo' ),
        'items_list'            => __( 'Daftar Promo', 'referral-store-promo' ),
        'items_list_navigation' => __( 'Navigasi daftar promo', 'referral-store-promo' ),
        'filter_items_list'     => __( 'Filter daftar promo', 'referral-store-promo' ),
    );
    $args = array(
        'label'                 => __( 'Promo Store', 'referral-store-promo' ),
        'description'           => __( 'Post Type untuk Promo Referral Toko.', 'referral-store-promo' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'thumbnail' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 20,
        'menu_icon'             => 'dashicons-tickets',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'rewrite'               => array( 'slug' => RSP_PLUGIN_SLUG, 'with_front' => true ),
        'capability_type'       => 'post',
        'show_in_rest'          => true,
    );
    register_post_type( RSP_PLUGIN_SLUG, $args );
}
add_action( 'init', 'rsp_register_promo_referral_cpt', 0 );

/**
 * Menambahkan Meta Box untuk Detail Promo.
 */
function rsp_add_promo_details_meta_box() {
    add_meta_box(
        'rsp_promo_details_meta_box_id',
        __( 'Detail Promo Referral', 'referral-store-promo' ),
        'rsp_render_promo_details_meta_box_content',
        RSP_PLUGIN_SLUG,
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'rsp_add_promo_details_meta_box' );

/**
 * Merender konten HTML untuk Meta Box Detail Promo.
 */
function rsp_render_promo_details_meta_box_content( $post ) {
    wp_nonce_field( 'rsp_save_promo_details_meta_data_action', 'rsp_promo_details_nonce' );

    $short_description = get_post_meta( $post->ID, '_rsp_promo_short_description', true );
    $start_date        = get_post_meta( $post->ID, '_rsp_promo_start_date', true );
    $end_date          = get_post_meta( $post->ID, '_rsp_promo_end_date', true );
    $terms_conditions  = get_post_meta( $post->ID, '_rsp_promo_terms_conditions', true );
    $promo_link        = get_post_meta( $post->ID, '_rsp_promo_link', true );

    ?>
<table class="form-table">
    <tr valign="top">
        <th scope="row">
            <label
                for="rsp_promo_short_description"><?php _e( 'Deskripsi Singkat Promo', 'referral-store-promo' ); ?></label>
        </th>
        <td>
            <textarea id="rsp_promo_short_description" name="rsp_promo_short_description" rows="4"
                class="large-text"><?php echo esc_textarea( $short_description ); ?></textarea>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">
            <label for="rsp_promo_start_date"><?php _e( 'Periode Mulai', 'referral-store-promo' ); ?></label>
        </th>
        <td>
            <input type="text" id="rsp_promo_start_date" name="rsp_promo_start_date"
                value="<?php echo esc_attr( $start_date ); ?>" class="regular-text" placeholder="YYYY-MM-DD" />
            <p class="description"><?php _e( 'Format: YYYY-MM-DD. Contoh: 2024-12-31', 'referral-store-promo' ); ?></p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">
            <label for="rsp_promo_end_date"><?php _e( 'Periode Berakhir', 'referral-store-promo' ); ?></label>
        </th>
        <td>
            <input type="text" id="rsp_promo_end_date" name="rsp_promo_end_date"
                value="<?php echo esc_attr( $end_date ); ?>" class="regular-text" placeholder="YYYY-MM-DD" />
            <p class="description"><?php _e( 'Format: YYYY-MM-DD. Contoh: 2025-01-31', 'referral-store-promo' ); ?></p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">
            <label for="rsp_promo_terms_conditions"><?php _e( 'Syarat & Ketentuan', 'referral-store-promo' ); ?></label>
        </th>
        <td>
            <textarea id="rsp_promo_terms_conditions" name="rsp_promo_terms_conditions" rows="6"
                class="large-text"><?php echo esc_textarea( $terms_conditions ); ?></textarea>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">
            <label for="rsp_promo_link"><?php _e( 'Link Tujuan Promo (Opsional)', 'referral-store-promo' ); ?></label>
        </th>
        <td>
            <input type="url" id="rsp_promo_link" name="rsp_promo_link" value="<?php echo esc_url( $promo_link ); ?>"
                class="large-text" placeholder="https://example.com/penawaran-spesial" />
        </td>
    </tr>
</table>
<?php
}

/**
 * Menyimpan data dari Meta Box Detail Promo saat post disimpan.
 */
function rsp_save_promo_details_meta_data( $post_id ) {
    if ( ! isset( $_POST['rsp_promo_details_nonce'] ) || ! wp_verify_nonce( $_POST['rsp_promo_details_nonce'], 'rsp_save_promo_details_meta_data_action' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( isset( $_POST['post_type'] ) && RSP_PLUGIN_SLUG == $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    } else {
        return;
    }

    $fields_to_save = array(
        'rsp_promo_short_description' => 'sanitize_textarea_field',
        'rsp_promo_start_date'        => 'sanitize_text_field',
        'rsp_promo_end_date'          => 'sanitize_text_field',
        'rsp_promo_terms_conditions'  => 'sanitize_textarea_field',
        'rsp_promo_link'              => 'esc_url_raw',
    );

    foreach ( $fields_to_save as $field_name => $sanitize_callback ) {
        if ( isset( $_POST[ $field_name ] ) ) {
            $value = call_user_func( $sanitize_callback, $_POST[ $field_name ] );
            update_post_meta( $post_id, '_' . $field_name, $value );
        }
    }
}
add_action( 'save_post', 'rsp_save_promo_details_meta_data' );

// --- MULAI KODE BARU UNTUK HALAMAN KLAIM VIRTUAL ---

/**
 * 1. Mendaftarkan Query Var untuk Klaim
 */
function rsp_claim_register_query_vars( $vars ) {
    $vars[] = 'rsp_promo_identifier'; // Query var untuk menangkap ID atau slug promo yang diklaim
    return $vars;
}
add_filter( 'query_vars', 'rsp_claim_register_query_vars' );

/**
 * 2. Menambahkan Rewrite Rule untuk Halaman Klaim Virtual
 * URL: situsanda.com/klaim-promo/{ID_ATAU_SLUG_PROMO}/
 */
function rsp_claim_rewrite_rules() {
    // Slug 'klaim-promo' bisa Anda ganti sesuai keinginan
    add_rewrite_rule(
        '^klaim-promo/([^/]+)/?$',
        'index.php?rsp_promo_identifier=$matches[1]',
        'top'
    );
}
// Tambahkan ke hook init bersama dengan registrasi CPT atau secara terpisah
add_action( 'init', 'rsp_claim_rewrite_rules' );


/**
 * 3. Menangani Request dan Memuat Template Halaman Klaim
 */
function rsp_claim_template_redirect() {
    $promo_identifier = get_query_var( 'rsp_promo_identifier' );

    if ( $promo_identifier ) {
        $sanitized_identifier = sanitize_text_field( $promo_identifier );

        $args_query = array(
            'post_type'      => RSP_PLUGIN_SLUG,
            'posts_per_page' => 1,
            'post_status'    => 'publish',
        );
        if ( is_numeric( $sanitized_identifier ) ) {
            $args_query['p'] = intval( $sanitized_identifier );
        } else {
            $args_query['name'] = $sanitized_identifier;
        }
        
        $promo_query = new WP_Query( $args_query );
        $promo_post_obj = null;

        if ( $promo_query->have_posts() ) {
            $promo_query->the_post(); // Setup $post global untuk digunakan di template jika perlu
            $promo_post_obj = $GLOBALS['post'];
            // Tidak perlu wp_reset_postdata() di sini jika template akan menggunakan $post global
        }

        // Jika promo tidak ditemukan, tampilkan 404
        if ( ! $promo_post_obj ) {
            global $wp_query;
            $wp_query->set_404();
            status_header( 404 );
            get_template_part( 404 );
            exit;
        }
        
        // --- LOGIKA AWAL SEBELUM MENAMPILKAN FORM ---
        // Misalnya, cek apakah promo masih dalam periode aktif
        $start_date_str = get_post_meta( $promo_post_obj->ID, '_rsp_promo_start_date', true );
        $end_date_str   = get_post_meta( $promo_post_obj->ID, '_rsp_promo_end_date', true );
        $current_date   = new DateTime('now', new DateTimeZone('Asia/Jakarta')); // Sesuaikan timezone jika perlu
        $is_active = true;
        $initial_message = '';

        if ($start_date_str) {
            $start_date = DateTime::createFromFormat('Y-m-d', $start_date_str);
            if ($start_date && $current_date < $start_date) {
                $is_active = false;
                $initial_message = __('Promo ini belum dimulai.', 'referral-store-promo');
            }
        }
        if ($is_active && $end_date_str) {
            $end_date = DateTime::createFromFormat('Y-m-d', $end_date_str);
            if ($end_date) {
                $end_date->setTime(23, 59, 59); // Anggap berakhir di akhir hari
                if ($current_date > $end_date) {
                    $is_active = false;
                    $initial_message = __('Promo ini sudah berakhir.', 'referral-store-promo');
                }
            }
        }
        // --- AKHIR LOGIKA AWAL ---

        status_header( 200 );

        // Siapkan data untuk dikirim ke template form klaim
        global $rsp_claim_form_data;
        $rsp_claim_form_data = array(
            'promo_obj'       => $promo_post_obj,
            'is_active'       => $is_active,        // Apakah promo aktif untuk ditampilkan formnya
            'initial_message' => $initial_message,  // Pesan jika promo tidak aktif
        );

        $template_path = RSP_PLUGIN_PATH . 'templates/klaim-promo-form.php'; // Ganti nama template
        if ( file_exists( $template_path ) ) {
            // Enqueue script di sini agar hanya load di halaman ini
            // Jika script.js Anda umum, enqueue seperti biasa sudah cukup.
            // Jika spesifik untuk dialpad, bisa lebih ditargetkan.
            // wp_enqueue_script('rsp-dialpad-logic', RSP_PLUGIN_URL . 'assets/js/dialpad-claim.js', array('jquery'), RSP_VERSION, true);

            include( $template_path );
            exit;
        } else {
            wp_die( 'Template form klaim tidak ditemukan: ' . esc_html($template_path) );
        }
    }
}
add_action( 'template_redirect', 'rsp_claim_template_redirect' );

// --- AKHIR KODE BARU UNTUK HALAMAN KLAIM VIRTUAL ---


function rsp_handle_ajax_process_claim() {
    // Verifikasi nonce untuk keamanan
    check_ajax_referer( 'rsp_claim_promo_nonce', 'security' );

    $kode_store = isset($_POST['kode_store']) ? sanitize_text_field($_POST['kode_store']) : '';
    $promo_id   = isset($_POST['promo_id']) ? intval($_POST['promo_id']) : 0;
    // $promo_slug = isset($_POST['promo_slug']) ? sanitize_text_field($_POST['promo_slug']) : ''; // Jika menggunakan slug

    if ( empty($kode_store) || $promo_id === 0 ) {
        wp_send_json_error(array('message' => __('Kode Store dan ID Promo diperlukan.', 'referral-store-promo')));
        return;
    }

    // Ambil detail promo lagi untuk validasi
    $promo_post = get_post($promo_id);

    if ( ! $promo_post || $promo_post->post_type !== RSP_PLUGIN_SLUG || $promo_post->post_status !== 'publish' ) {
        wp_send_json_error(array('message' => __('Promo tidak valid atau tidak ditemukan.', 'referral-store-promo')));
        return;
    }

    // --- DI SINI LOGIKA PROSES KLAIM SPESIFIK ANDA DENGAN KODE STORE ---
    // Contoh:
    // 1. Validasi Kode Store (misalnya, cek ke database apakah kode store ini valid/terdaftar)
    //    $is_kode_store_valid = my_custom_validate_store_code($kode_store);
    //    if (!$is_kode_store_valid) {
    //        wp_send_json_error(array('message' => __('Kode Store tidak valid.', 'referral-store-promo')));
    //        return;
    //    }

    // 2. Cek apakah user sudah login (jika perlu)
    //    if (!is_user_logged_in()) {
    //        wp_send_json_error(array('message' => __('Anda harus login untuk melakukan klaim.', 'referral-store-promo')));
    //        return;
    //    }
    //    $user_id = get_current_user_id();

    // 3. Cek apakah promo masih dalam periode aktif (double check)
    $start_date_str = get_post_meta( $promo_post->ID, '_rsp_promo_start_date', true );
    $end_date_str   = get_post_meta( $promo_post->ID, '_rsp_promo_end_date', true );
    $current_date   = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
    $is_active = true;
    if ($start_date_str) {
        $start_date = DateTime::createFromFormat('Y-m-d', $start_date_str);
        if ($start_date && $current_date < $start_date) $is_active = false;
    }
    if ($is_active && $end_date_str) {
        $end_date = DateTime::createFromFormat('Y-m-d', $end_date_str);
        if ($end_date) { $end_date->setTime(23,59,59); if ($current_date > $end_date) $is_active = false; }
    }
    if (!$is_active) {
        wp_send_json_error(array('message' => __('Periode promo sudah berakhir atau belum dimulai.', 'referral-store-promo')));
        return;
    }

    // 4. Cek apakah user ini sudah pernah klaim promo ini dengan kode store ini (jika ada batasan)
    //    $already_claimed = check_if_user_already_claimed($user_id, $promo_id, $kode_store);
    //    if ($already_claimed) {
    //        wp_send_json_error(array('message' => __('Anda sudah pernah mengklaim promo ini dengan kode store tersebut.', 'referral-store-promo')));
    //        return;
    //    }

    // 5. Lakukan aksi klaim (simpan ke DB, kirim email, dll.)
    //    Contoh: simpan_data_klaim($user_id, $promo_id, $kode_store, time());

    // Jika semua berhasil:
    $success_message = sprintf(
        __('Selamat! Anda berhasil mengklaim promo "%s" dengan Kode Store %s.', 'referral-store-promo'),
        esc_html($promo_post->post_title),
        esc_html($kode_store)
    );
    // Tambahkan detail lain jika perlu, misal kode voucher unik yang digenerate
    // $unique_voucher_code = generate_unique_code();
    // $response_data = array(
    //     'message' => $success_message,
    //     'claimed_voucher_code' => $unique_voucher_code 
    // );
    // wp_send_json_success($response_data);

    wp_send_json_success(array('message' => $success_message));
}
// Hook untuk user yang login
add_action( 'wp_ajax_rsp_process_claim_with_store_code', 'rsp_handle_ajax_process_claim' );
// Hook untuk user yang tidak login (jika klaim diizinkan untuk non-login user)
add_action( 'wp_ajax_nopriv_rsp_process_claim_with_store_code', 'rsp_handle_ajax_process_claim' );



/**
 * Flush rewrite rules pada saat aktivasi plugin.
 */
function rsp_plugin_activation() {
    rsp_register_promo_referral_cpt(); // Pastikan CPT terdaftar
    rsp_claim_rewrite_rules();         // Daftarkan rewrite rule klaim kita
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'rsp_plugin_activation' );

/**
 * Flush rewrite rules juga pada saat deaktivasi.
 */
function rsp_plugin_deactivation() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'rsp_plugin_deactivation' );

/**
 * Memuat file terjemahan.
 */
function rsp_load_textdomain() {
    load_plugin_textdomain( 'referral-store-promo', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); // Tambahkan folder /languages/ jika ada
}
add_action( 'plugins_loaded', 'rsp_load_textdomain' );

/**
 * Memuat template kustom dari plugin untuk Promo Store.
 */
function rsp_include_custom_template( $template ) {
    if ( is_singular( RSP_PLUGIN_SLUG ) ) {
        $plugin_template = RSP_PLUGIN_PATH . 'templates/single-' . RSP_PLUGIN_SLUG . '.php'; // Nama file template harus cocok dengan slug CPT
        if ( file_exists( $plugin_template ) ) {
            return $plugin_template;
        }
    } elseif ( is_post_type_archive( RSP_PLUGIN_SLUG ) ) {
        $plugin_template = RSP_PLUGIN_PATH . 'templates/archive-' . RSP_PLUGIN_SLUG . '.php'; // Nama file template arsip
        if ( file_exists( $plugin_template ) ) {
            return $plugin_template;
        }
    }
    return $template;
}
add_filter( 'template_include', 'rsp_include_custom_template' );



$script_vars = array(
    'ajax_url' => admin_url( 'admin-ajax.php' ),
    'nonce' => function_exists('wp_create_nonce') ? wp_create_nonce('rsp_claim_promo_nonce') : '', // Nama nonce harus sama dengan yang dicek di AJAX handler
    'submitting_text' => __('Memproses...', 'referral-store-promo'),
    'submit_text' => __('Submit', 'referral-store-promo'),
    'error_kode_store_empty' => __('Kode Store tidak boleh kosong.', 'referral-store-promo'),
    'error_generic' => __('Terjadi kesalahan. Silakan coba lagi.', 'referral-store-promo'),
    'error_ajax' => __('Kesalahan koneksi. Silakan coba lagi.', 'referral-store-promo'),
);
wp_localize_script( 'rsp-frontend-scripts', 'rsp_script_vars', $script_vars );

?>