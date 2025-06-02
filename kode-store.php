<?php
/**
 * Plugin Name:      Referral Store Promo
 * Description:      Promo List untuk Referal Store
 * Version:          1.2.1 // Versi dinaikkan untuk menandai perbaikan
 * Author:           PT Doran Sukses Indonesia
 * Author URI:       https://doran.id/
 * License:          GPL v2 or later
 * License URI:      https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:      referral-store-promo
 * Domain Path:      /languages
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'RSP_VERSION', '1.2.1' );
define( 'RSP_PLUGIN_SLUG', 'promo_referral' ); // Ini adalah slug CPT Anda

// Definisikan konstanta path plugin jika belum ada
if ( ! defined( 'RSP_PLUGIN_PATH' ) ) {
    define( 'RSP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'RSP_PLUGIN_URL' ) ) {
    define( 'RSP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Mendaftarkan dan memuat aset (CSS & JS).
 */
function rsp_enqueue_frontend_assets() { // Mengganti nama fungsi agar lebih umum
    $is_promo_archive_or_single = is_singular(RSP_PLUGIN_SLUG) || is_post_type_archive(RSP_PLUGIN_SLUG);
    
    // Cek apakah ini halaman klaim virtual kita berdasarkan query var
    $promo_identifier_for_claim = get_query_var('rsp_promo_identifier');
    $is_klaim_form_page = !empty($promo_identifier_for_claim);

    // Kondisi untuk halaman konfirmasi klaim (jika menggunakan page template dari tema)
    // Ganti 'templates/klaim-promo-konfirmasi.php' dengan path yang benar jika ada di subdirektori tema
    $is_klaim_konfirmasi_page = is_page_template('templates/klaim-promo-konfirmasi.php'); 

    // Aset untuk halaman arsip CPT, single CPT, dan mungkin halaman konfirmasi
    if ( $is_promo_archive_or_single || $is_klaim_konfirmasi_page ) {
        wp_enqueue_style(
            'rsp-frontend-styles', // Handle untuk CSS umum
            RSP_PLUGIN_URL . 'assets/css/style.css',
            array(),
            RSP_VERSION
        );
    
        wp_enqueue_script(
            'rsp-frontend-scripts', // Handle untuk JS umum
            RSP_PLUGIN_URL . 'assets/js/script.js',
            array('jquery'), 
            RSP_VERSION,
            true
        );

        // Variabel umum untuk script.js (jika ada)
        // Misalnya, jika script.js juga melakukan panggilan AJAX
        $general_script_vars = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('rsp_general_ajax_nonce'), // Buat nonce berbeda jika aksinya beda
        );
        // Gunakan nama objek yang sesuai untuk script.js Anda, misal 'rsp_general_params' atau 'rsp_script_vars'
        wp_localize_script('rsp-frontend-scripts', 'rsp_general_params', $general_script_vars);
    }

    // Aset khusus untuk halaman klaim-promo-form (Dial Pad)
    if ( $is_klaim_form_page ) {
        wp_enqueue_style(
            'rsp-frontend-dial-styles',
            RSP_PLUGIN_URL . 'assets/css/dial-promo.css',
            array(),
            RSP_VERSION
        );
        
        wp_enqueue_script(
            'rsp-frontend-dial-scripts',
            RSP_PLUGIN_URL . 'assets/js/dial-promo.js',
            array('jquery'), // Pastikan jQuery memang dibutuhkan oleh dial-promo.js
            RSP_VERSION,
            true
        );

        // Ambil data promo langsung di sini untuk dilokalisasi ke dial-promo.js
        $promo_post_for_localize = null;
        $kode_promo_value_localize = '';
        $is_active_localize = false; 
        $initial_message_localize = __('Promo tidak ditemukan atau tidak valid.', 'referral-store-promo');

        if ($promo_identifier_for_claim) { // Pastikan identifier ada
            $sanitized_identifier = sanitize_text_field($promo_identifier_for_claim);

            $args_query_localize = array(
                'post_type'      => RSP_PLUGIN_SLUG,
                'posts_per_page' => 1,
                'post_status'    => 'publish',
            );
            if (is_numeric($sanitized_identifier)) {
                $args_query_localize['p'] = intval($sanitized_identifier);
            } else {
                $args_query_localize['name'] = $sanitized_identifier;
            }
            $promo_query_localize = new WP_Query($args_query_localize);

            if ($promo_query_localize->have_posts()) {
                $promo_post_for_localize = $promo_query_localize->posts[0];
            }
        }
        
        if ($promo_post_for_localize) {
            if (property_exists($promo_post_for_localize, 'post_name')) {
                $kode_promo_value_localize = $promo_post_for_localize->post_name;
            }

            $start_date_str_localize = get_post_meta($promo_post_for_localize->ID, '_rsp_promo_start_date', true);
            $end_date_str_localize   = get_post_meta($promo_post_for_localize->ID, '_rsp_promo_end_date', true);
            $current_date_localize   = new DateTime('now', new DateTimeZone('Asia/Jakarta')); // Sesuaikan timezone
            $is_active_localize = true; 

            if ($start_date_str_localize) {
                $start_date_localize = DateTime::createFromFormat('Y-m-d', $start_date_str_localize);
                if ($start_date_localize && $current_date_localize < $start_date_localize) {
                    $is_active_localize = false;
                    $initial_message_localize = __('Promo ini belum dimulai.', 'referral-store-promo');
                }
            }
            if ($is_active_localize && $end_date_str_localize) {
                $end_date_localize = DateTime::createFromFormat('Y-m-d', $end_date_str_localize);
                if ($end_date_localize) {
                    $end_date_localize->setTime(23, 59, 59);
                    if ($current_date_localize > $end_date_localize) {
                        $is_active_localize = false;
                        $initial_message_localize = __('Promo ini sudah berakhir.', 'referral-store-promo');
                    }
                }
            }
            if ($is_active_localize) { 
                 $initial_message_localize = ''; // Kosongkan jika aktif
            }
        }
        
        $dial_script_vars = array(
            'ajax_url'               => admin_url('admin-ajax.php'),
            'nonce'                  => wp_create_nonce('rsp_claim_promo_nonce'), // Nonce untuk AJAX handler klaim
            'jsKodePromo'            => $kode_promo_value_localize,
            'isPromoActive'          => $is_active_localize,       
            'initialPromoMessage'    => $initial_message_localize, 
            'error_kode_store_empty' => __('Kode Store tidak boleh kosong.', 'referral-store-promo'),
            'submitting_text'        => __('Memproses...', 'referral-store-promo'),
            'webhookUrl'             => 'https://services.leadconnectorhq.com/hooks/EBB7zornJZkBodHpGN3B/webhook-trigger/fd58e12a-3741-4b67-a6b5-81ae48981be2', // Sebaiknya disimpan sebagai opsi plugin
            'thankYouPageUrl'        => site_url('/thank-you/'), // Pastikan halaman ini ada
            'error_generic'          => __('Terjadi kesalahan. Silakan coba lagi.', 'referral-store-promo'),
            'error_ajax'             => __('Kesalahan koneksi. Silakan coba lagi.', 'referral-store-promo'),
        );
        wp_localize_script('rsp-frontend-dial-scripts', 'rsp_params', $dial_script_vars); 
    }
}
add_action('wp_enqueue_scripts', 'rsp_enqueue_frontend_assets');


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
        'supports'              => array( 'title', 'thumbnail' ), // Hanya judul dan banner
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 20,
        'menu_icon'             => 'dashicons-tickets',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true, // Aktifkan halaman arsip (daftar semua promo)
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'rewrite'               => array( 'slug' => RSP_PLUGIN_SLUG, 'with_front' => true ),
        'capability_type'       => 'post',
        'show_in_rest'          => true, // Untuk Gutenberg atau REST API
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
        RSP_PLUGIN_SLUG, // Tampilkan hanya di CPT 'promo_referral'
        'normal', // 'normal', 'side', 'advanced'
        'high'    // 'high', 'core', 'default', 'low'
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
                <label for="rsp_promo_short_description"><?php _e( 'Deskripsi Singkat Promo', 'referral-store-promo' ); ?></label>
            </th>
            <td>
                <textarea id="rsp_promo_short_description" name="rsp_promo_short_description" rows="4" class="large-text"><?php echo esc_textarea( $short_description ); ?></textarea>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">
                <label for="rsp_promo_start_date"><?php _e( 'Periode Mulai', 'referral-store-promo' ); ?></label>
            </th>
            <td>
                <input type="date" id="rsp_promo_start_date" name="rsp_promo_start_date" value="<?php echo esc_attr( $start_date ); ?>" class="regular-text" placeholder="YYYY-MM-DD" />
                <p class="description"><?php _e( 'Format: YYYY-MM-DD. Contoh: 2024-12-31', 'referral-store-promo' ); ?></p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">
                <label for="rsp_promo_end_date"><?php _e( 'Periode Berakhir', 'referral-store-promo' ); ?></label>
            </th>
            <td>
                <input type="date" id="rsp_promo_end_date" name="rsp_promo_end_date" value="<?php echo esc_attr( $end_date ); ?>" class="regular-text" placeholder="YYYY-MM-DD" />
                <p class="description"><?php _e( 'Format: YYYY-MM-DD. Contoh: 2025-01-31', 'referral-store-promo' ); ?></p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">
                <label for="rsp_promo_terms_conditions"><?php _e( 'Syarat & Ketentuan', 'referral-store-promo' ); ?></label>
            </th>
            <td>
                <?php
                // Menggunakan wp_editor untuk S&K agar mendukung rich text
                wp_editor( $terms_conditions, 'rsp_promo_terms_conditions', array(
                    'textarea_name' => 'rsp_promo_terms_conditions',
                    'media_buttons' => false,
                    'textarea_rows' => 10,
                    'teeny'         => true, // Opsi untuk editor yang lebih simpel
                ) );
                ?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">
                <label for="rsp_promo_link"><?php _e( 'Link Tujuan Promo (Opsional)', 'referral-store-promo' ); ?></label>
            </th>
            <td>
                <input type="url" id="rsp_promo_link" name="rsp_promo_link" value="<?php echo esc_url( $promo_link ); ?>" class="large-text" placeholder="https://example.com/penawaran-spesial" />
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
    // Pastikan ini adalah CPT kita sebelum menyimpan
    if ( ! isset( $_POST['post_type'] ) || RSP_PLUGIN_SLUG != $_POST['post_type'] ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Sanitasi dan simpan data
    if ( isset( $_POST['rsp_promo_short_description'] ) ) {
        update_post_meta( $post_id, '_rsp_promo_short_description', sanitize_textarea_field( $_POST['rsp_promo_short_description'] ) );
    }
    if ( isset( $_POST['rsp_promo_start_date'] ) ) {
        update_post_meta( $post_id, '_rsp_promo_start_date', sanitize_text_field( $_POST['rsp_promo_start_date'] ) );
    }
    if ( isset( $_POST['rsp_promo_end_date'] ) ) {
        update_post_meta( $post_id, '_rsp_promo_end_date', sanitize_text_field( $_POST['rsp_promo_end_date'] ) );
    }
    if ( isset( $_POST['rsp_promo_terms_conditions'] ) ) {
        // wp_kses_post digunakan untuk membersihkan input dari wp_editor
        update_post_meta( $post_id, '_rsp_promo_terms_conditions', wp_kses_post( $_POST['rsp_promo_terms_conditions'] ) );
    }
    if ( isset( $_POST['rsp_promo_link'] ) ) {
        update_post_meta( $post_id, '_rsp_promo_link', esc_url_raw( $_POST['rsp_promo_link'] ) );
    }
}
add_action( 'save_post', 'rsp_save_promo_details_meta_data' );

// --- KODE UNTUK HALAMAN KLAIM VIRTUAL ---

/**
 * Mendaftarkan Query Var untuk Klaim.
 */
function rsp_claim_register_query_vars( $vars ) {
    $vars[] = 'rsp_promo_identifier';
    return $vars;
}
add_filter( 'query_vars', 'rsp_claim_register_query_vars' );

/**
 * Menambahkan Rewrite Rule untuk Halaman Klaim Virtual.
 */
function rsp_claim_rewrite_rules() {
    add_rewrite_rule(
        '^klaim-promo/([^/]+)/?$', // URL: situsanda.com/klaim-promo/slug-atau-id-promo/
        'index.php?rsp_promo_identifier=$matches[1]',
        'top'
    );
}
add_action( 'init', 'rsp_claim_rewrite_rules' );


/**
 * Menangani Request dan Memuat Template Halaman Klaim.
 */
function rsp_claim_template_redirect() {
    $promo_identifier = get_query_var( 'rsp_promo_identifier' );

    if ( $promo_identifier ) {
        $sanitized_identifier = sanitize_text_field( $promo_identifier );
        $promo_post_obj = null;

        $args_query = array(
            'post_type'      => RSP_PLUGIN_SLUG,
            'posts_per_page' => 1,
            'post_status'    => 'publish',
        );
        if ( is_numeric( $sanitized_identifier ) ) {
            $args_query['p'] = intval( $sanitized_identifier );
        } else {
            $args_query['name'] = $sanitized_identifier; // 'name' adalah untuk post slug
        }
        
        $promo_query = new WP_Query( $args_query );

        if ( $promo_query->have_posts() ) {
            $promo_query->the_post(); // Setup $post global
            $promo_post_obj = $GLOBALS['post']; // Ambil objek post global yang sudah di-setup
        }
        // Tidak perlu wp_reset_postdata() di sini karena template akan menggunakan $post global
        // Namun, jika ada loop lain setelah ini di hook yang sama, pertimbangkan untuk mereset.

        if ( ! $promo_post_obj ) {
            global $wp_query;
            $wp_query->set_404();
            status_header( 404 );
            get_template_part( 404 ); // Muat template 404 dari tema
            exit;
        }
        
        // Siapkan data untuk template form klaim (ini bisa diakses di template via global)
        // Logika ini sekarang juga dilakukan di rsp_enqueue_frontend_assets untuk wp_localize_script
        // Tapi kita tetap siapkan di sini untuk template PHP itu sendiri
        global $rsp_claim_form_data;
        $start_date_str = get_post_meta( $promo_post_obj->ID, '_rsp_promo_start_date', true );
        $end_date_str   = get_post_meta( $promo_post_obj->ID, '_rsp_promo_end_date', true );
        $current_date   = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
        $is_active_for_template = true;
        $initial_message_for_template = '';

        if ($start_date_str) {
            $start_date = DateTime::createFromFormat('Y-m-d', $start_date_str);
            if ($start_date && $current_date < $start_date) {
                $is_active_for_template = false;
                $initial_message_for_template = __('Promo ini belum dimulai.', 'referral-store-promo');
            }
        }
        if ($is_active_for_template && $end_date_str) {
            $end_date = DateTime::createFromFormat('Y-m-d', $end_date_str);
            if ($end_date) {
                $end_date->setTime(23, 59, 59);
                if ($current_date > $end_date) {
                    $is_active_for_template = false;
                    $initial_message_for_template = __('Promo ini sudah berakhir.', 'referral-store-promo');
                }
            }
        }
         if ($is_active_for_template) { 
            $initial_message_for_template = '';
        }

        $rsp_claim_form_data = array(
            'promo_obj'       => $promo_post_obj,
            'is_active'       => $is_active_for_template,
            'initial_message' => $initial_message_for_template,
        );
        
        status_header( 200 ); // Set status header ke 200 OK
        $template_path = RSP_PLUGIN_PATH . 'templates/klaim-promo-form.php';
        if ( file_exists( $template_path ) ) {
            include( $template_path );
            exit;
        } else {
            wp_die( 'Template form klaim tidak ditemukan: ' . esc_html($template_path) );
        }
    }
}
add_action( 'template_redirect', 'rsp_claim_template_redirect' );

/**
 * Handler untuk AJAX proses klaim. (Ini adalah contoh, sesuaikan dengan kebutuhan dial pad Anda)
 * Dial pad Anda saat ini mengirim langsung ke webhook LeadConnector, jadi fungsi AJAX ini mungkin tidak digunakan oleh dial pad.
 * Namun, saya tetap sertakan jika Anda membutuhkannya untuk validasi sisi server sebelum ke webhook, atau untuk sistem klaim lain.
 */
function rsp_handle_ajax_process_claim_server_side() {
    check_ajax_referer( 'rsp_claim_promo_nonce', 'security_nonce_name' ); // Ganti 'security_nonce_name' dengan nama field nonce dari JS

    $kode_store = isset($_POST['kode_store']) ? sanitize_text_field($_POST['kode_store']) : '';
    $promo_slug = isset($_POST['promo_slug']) ? sanitize_text_field($_POST['promo_slug']) : ''; // atau promo_id
    // $no_whatsapp = isset($_POST['no_whatsapp']) ? sanitize_text_field($_POST['no_whatsapp']) : '';

    if ( empty($kode_store) || empty($promo_slug) ) { // || empty($no_whatsapp)
        wp_send_json_error(array('message' => __('Data tidak lengkap.', 'referral-store-promo')));
        return;
    }

    $promo_post = get_page_by_path($promo_slug, OBJECT, RSP_PLUGIN_SLUG);

    if ( ! $promo_post || $promo_post->post_status !== 'publish' ) {
        wp_send_json_error(array('message' => __('Promo tidak valid.', 'referral-store-promo')));
        return;
    }

    // Validasi periode promo (contoh, Anda sudah punya ini di atas)
    // ...

    // Logika validasi Kode Store Anda di sini jika perlu
    // ...

    // Jika semua validasi sisi server OK, Anda bisa lanjutkan.
    // Saat ini, dial pad langsung mengirim ke webhook. Jika Anda ingin validasi server dulu:
    // 1. JS kirim ke AJAX handler ini.
    // 2. Handler ini validasi.
    // 3. Jika OK, handler ini bisa:
    //    a. Meneruskan data ke webhook LeadConnector dari sisi server (lebih aman).
    //    b. Atau, kirim respons sukses ke JS, lalu JS redirect atau kirim ke webhook.

    wp_send_json_success(array('message' => __('Validasi server berhasil (contoh).', 'referral-store-promo')));
}
add_action( 'wp_ajax_rsp_process_server_claim', 'rsp_handle_ajax_process_claim_server_side' );
add_action( 'wp_ajax_nopriv_rsp_process_server_claim', 'rsp_handle_ajax_process_claim_server_side' );


/**
 * Flush rewrite rules pada saat aktivasi dan deaktivasi plugin.
 */
function rsp_plugin_activation() {
    rsp_register_promo_referral_cpt();
    rsp_claim_rewrite_rules();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'rsp_plugin_activation' );

function rsp_plugin_deactivation() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'rsp_plugin_deactivation' );

/**
 * Memuat file terjemahan.
 */
function rsp_load_textdomain_plugin() { // Nama fungsi diubah agar unik
    load_plugin_textdomain( 'referral-store-promo', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'rsp_load_textdomain_plugin' );

/**
 * Memuat template kustom dari plugin untuk CPT Promo Store.
 */
function rsp_include_custom_cpt_template( $template ) { // Nama fungsi diubah agar unik
    if ( is_singular( RSP_PLUGIN_SLUG ) ) {
        $plugin_template = RSP_PLUGIN_PATH . 'templates/single-' . RSP_PLUGIN_SLUG . '.php';
        if ( file_exists( $plugin_template ) ) {
            return $plugin_template;
        }
    } elseif ( is_post_type_archive( RSP_PLUGIN_SLUG ) ) {
        $plugin_template = RSP_PLUGIN_PATH . 'templates/archive-' . RSP_PLUGIN_SLUG . '.php';
        if ( file_exists( $plugin_template ) ) {
            return $plugin_template;
        }
    }
    return $template;
}
add_filter( 'template_include', 'rsp_include_custom_cpt_template' );

?>