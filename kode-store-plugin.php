<?php
/**
 * Plugin Name:      Kode Store Promo
 * Description:      Promo dengan Input Kode Store
 * Version:          1.3.0
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

define( 'RSP_VERSION', '1.3.0' );
define( 'RSP_PLUGIN_SLUG', 'promo_referral' ); // Ini adalah slug CPT Anda

// Definisikan konstanta path plugin jika belum ada
if ( ! defined( 'RSP_PLUGIN_PATH' ) ) {
    define( 'RSP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'RSP_PLUGIN_URL' ) ) {
    define( 'RSP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

// Table untuk simpan data Klaim Promo
register_activation_hook(__FILE__, 'create_table_klaim_promo');

function create_table_klaim_promo() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'klaim_promo';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        nama_store varchar(100) NOT NULL,
        no_whatsapp varchar(100) NOT NULL,
        kode_store varchar(100) NOT NULL,
        kode_promo varchar(100) NOT NULL,
        tanggal_klaim datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}


add_action('wp_ajax_rsp_claim_promo', 'handle_rsp_claim_promo');
add_action('wp_ajax_nopriv_rsp_claim_promo', 'handle_rsp_claim_promo');

function handle_rsp_claim_promo() {
    check_ajax_referer('rsp_claim_promo_nonce', 'nonce');

    $kode_store  = sanitize_text_field($_POST['kode_store'] ?? '');
    $no_whatsapp = sanitize_text_field($_POST['nomor_wa'] ?? '');
    $kode_promo  = sanitize_text_field($_POST['kode_promo'] ?? '');
    $nama_store  = sanitize_text_field($_POST['nama_store'] ?? '');

    if (empty($kode_store) || empty($no_whatsapp) || empty($kode_promo) || empty($nama_store)) {
        wp_send_json_error('Data tidak lengkap.');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'klaim_promo';

    $result = $wpdb->insert(
        $table_name,
        [
            'nama_store'     => $nama_store,
            'no_whatsapp'    => $no_whatsapp,
            'kode_store'     => $kode_store,
            'kode_promo'     => $kode_promo,
            'tanggal_klaim'  => current_time('mysql'),
        ],
        [ '%s', '%s', '%s', '%s', '%s' ]
    );

    if ($result === false) {
        wp_send_json_error('Gagal menyimpan data klaim.');
    }

    wp_send_json_success('Berhasil disimpan.');
}


/**
 * Mendaftarkan dan memuat aset (CSS & JS) Frontend.
 */

 if (!function_exists('rsp_register_claims_report_submenu_page')) {
    function rsp_register_claims_report_submenu_page() {
        add_submenu_page(
            'edit.php?post_type=' . RSP_PLUGIN_SLUG, // Parent slug: Halaman list CPT "Promo Store"
            __('Laporan Klaim Promo', 'referral-store-promo'),   // Judul halaman (muncul di tag <title>)
            __('Laporan', 'referral-store-promo'),               // Judul menu submenu
            'manage_options',                                    // Capability yang dibutuhkan untuk melihat menu ini
            'rsp-claims-report',                                 // Menu slug (unik untuk halaman submenu ini)
            'rsp_render_claims_admin_page'                       // Fungsi callback untuk merender konten halaman
            // Anda bisa menambahkan parameter $position jika ingin mengatur urutan submenu
        );
    }
    add_action('admin_menu', 'rsp_register_claims_report_submenu_page');
}

// Pastikan fungsi callback ini sudah ada (dari jawaban sebelumnya):
if (!function_exists('rsp_render_claims_admin_page')) {
    function rsp_render_claims_admin_page() {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Laporan Klaim Promo Store', 'referral-store-promo') . '</h1>';

        // Memuat class WP_List_Table dan class turunan Anda
        if (!class_exists('WP_List_Table')) {
            require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
        }
        // Pastikan path ke class-rsp-claims-list-table.php sudah benar
        $list_table_path = RSP_PLUGIN_PATH . 'includes/class-rsp-claims-list-table.php';
        if (file_exists($list_table_path) && !class_exists('RSP_Claims_List_Table')) {
            require_once($list_table_path);
        } else if (!file_exists($list_table_path)) {
            echo '<div class="error"><p>' . sprintf( esc_html__('File %s tidak ditemukan. Tabel laporan tidak bisa ditampilkan.', 'referral-store-promo'), '<code>includes/class-rsp-claims-list-table.php</code>') . '</p></div>';
            echo '</div>'; // Tutup div.wrap
            return;
        }


        if (class_exists('RSP_Claims_List_Table')) {
            $claims_table = new RSP_Claims_List_Table();
            // Panggil process_bulk_action() JIKA Anda mengimplementasikannya di class RSP_Claims_List_Table
            // if (method_exists($claims_table, 'process_bulk_action')) {
            //    $claims_table->process_bulk_action();
            // }
            $claims_table->prepare_items();

            echo '<form method="post">';
            // Input tersembunyi untuk WP_List_Table agar search dan pagination berfungsi dengan benar
            // dalam konteks halaman admin WordPress.
            echo '<input type="hidden" name="page" value="' . esc_attr(isset($_REQUEST['page']) ? $_REQUEST['page'] : '') . '" />';
            $claims_table->search_box( __('Cari Klaim','referral-store-promo'), 'search_claim_id' );
            $claims_table->display();
            echo '</form>';
        } else {
             echo '<div class="error"><p>' . esc_html__('Class RSP_Claims_List_Table tidak ditemukan. Tabel laporan tidak bisa ditampilkan.', 'referral-store-promo') . '</p></div>';
        }

        echo '</div>'; // Tutup div.wrap
    }
}

function rsp_enqueue_frontend_assets() {
    $is_promo_archive_or_single = is_singular(RSP_PLUGIN_SLUG) || is_post_type_archive(RSP_PLUGIN_SLUG);
    $promo_identifier_for_claim = get_query_var('rsp_promo_identifier');
    $is_klaim_form_page = !empty($promo_identifier_for_claim);
    $is_klaim_konfirmasi_page = is_page_template('templates/klaim-promo-konfirmasi.php');

    if ( $is_promo_archive_or_single || $is_klaim_konfirmasi_page ) {
        wp_enqueue_style(
            'rsp-frontend-styles',
            RSP_PLUGIN_URL . 'assets/css/style.css',
            array(),
            RSP_VERSION
        );

        wp_enqueue_script(
            'rsp-frontend-scripts',
            RSP_PLUGIN_URL . 'assets/js/script.js',
            array('jquery'),
            RSP_VERSION,
            true
        );

        $general_script_vars = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('rsp_general_ajax_nonce'),
        );
        wp_localize_script('rsp-frontend-scripts', 'rsp_general_params', $general_script_vars);
    }

    if ( $is_klaim_form_page ) {
        wp_enqueue_style(
            'rsp-frontend-dial-styles',
            RSP_PLUGIN_URL . 'assets/css/dial-promo.css',
            array(),
            RSP_VERSION
        );

        wp_enqueue_script(
            'rsp-frontend-dial-scripts', // Handle untuk script dial pad
            RSP_PLUGIN_URL . 'assets/js/dial-promo.js',
            array('jquery'),
            RSP_VERSION,
            true
        );

        $promo_post_for_localize = null;
        $kode_promo_value_localize = '';
        $is_active_localize = false;
        $initial_message_localize = __('Promo tidak ditemukan atau tidak valid.', 'referral-store-promo');

        if ($promo_identifier_for_claim) {
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
            $current_date_localize   = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
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
                 $initial_message_localize = '';
            }
        }

        $dial_script_vars = array(
            'ajax_url'               => admin_url('admin-ajax.php'),
            'nonce'                  => wp_create_nonce('rsp_claim_promo_nonce'), // Nonce untuk AJAX validasi kode store & klaim
            'jsKodePromo'            => $kode_promo_value_localize,
            'isPromoActive'          => $is_active_localize,
            'initialPromoMessage'    => $initial_message_localize,
            'error_kode_store_empty' => __('Kode Store tidak boleh kosong.', 'referral-store-promo'),
            'submitting_text'        => __('Memproses...', 'referral-store-promo'),
            'validating_text'        => __('Memvalidasi...', 'referral-store-promo'), // Tambahkan ini jika belum ada di JS
            'webhookUrl'             => 'https://services.leadconnectorhq.com/hooks/EBB7zornJZkBodHpGN3B/webhook-trigger/fd58e12a-3741-4b67-a6b5-81ae48981be2',
            'thankYouPageUrl'        => site_url('/thank-you/'),
            'error_generic'          => __('Terjadi kesalahan. Silakan coba lagi.', 'referral-store-promo'),
            'error_ajax'             => __('Kesalahan koneksi. Silakan coba lagi.', 'referral-store-promo'),
            // Tambahkan nonce untuk save_data jika Anda menggunakannya
            // 'save_data_nonce' => wp_create_nonce('rsp_save_data_nonce_action_js'),
        );
        // Nama objek JS harus 'rsp_params' agar sesuai dengan yang digunakan JS di template klaim-promo-form.php
        wp_localize_script('rsp-frontend-dial-scripts', 'rsp_params', $dial_script_vars);
    }
}
add_action('wp_enqueue_scripts', 'rsp_enqueue_frontend_assets');

function rsp_admin_enqueue_assets( $hook_suffix ) {
    global $post_type;
    if ( ('post.php' == $hook_suffix || 'post-new.php' == $hook_suffix) && RSP_PLUGIN_SLUG == $post_type ) {
        wp_enqueue_media();
    }
}
add_action( 'admin_enqueue_scripts', 'rsp_admin_enqueue_assets' );

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
        // 'promo_report'          => __( 'Laporan Klaim Promo', 'referral-store-promo' ),
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

function rsp_render_promo_details_meta_box_content( $post ) {
    wp_nonce_field( 'rsp_save_promo_details_meta_data_action', 'rsp_promo_details_nonce' );

    $short_description = get_post_meta( $post->ID, '_rsp_promo_short_description', true );
    $description = get_post_meta( $post->ID, '_rsp_promo_description', true );
    $start_date        = get_post_meta( $post->ID, '_rsp_promo_start_date', true );
    $end_date          = get_post_meta( $post->ID, '_rsp_promo_end_date', true );
    $terms_conditions  = get_post_meta( $post->ID, '_rsp_promo_terms_conditions', true );
    $promo_link        = get_post_meta( $post->ID, '_rsp_promo_link', true );
    $promo_image_id    = get_post_meta( $post->ID, '_rsp_promo_custom_image_id', true );
    $promo_image_url   = '';
    if ( $promo_image_id ) {
        $promo_image_url = wp_get_attachment_image_url( $promo_image_id, 'medium' );
    }
    ?>
<table class="form-table">
    <tr valign="top">
        <th scope="row">
            <label
                for="rsp_promo_short_description"><?php _e( 'Deskripsi Singkat Promo', 'referral-store-promo' ); ?></label>
        </th>
        <td>
            <textarea id="rsp_promo_short_description" name="rsp_promo_short_description" rows="3"
                class="large-text"><?php echo esc_textarea( $short_description ); ?></textarea>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">
            <label for="rsp_promo_description"><?php _e( 'Deskripsi Promo', 'referral-store-promo' ); ?></label>
        </th>
        <td>
            <?php
                wp_editor( $description , 'rsp_promo_description', array(
                    'textarea_name' => 'rsp_promo_description',
                    'media_buttons' => true,
                    'textarea_rows' => 10,
                    'teeny'         => true,
                ) );
                ?>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">
            <label
                for="rsp_promo_custom_image_button"><?php _e( 'Gambar Product Promo', 'referral-store-promo' ); ?></label>
        </th>
        <td>
            <div class="rsp-image-uploader-wrapper">
                <input type="hidden" id="rsp_promo_custom_image_id" name="rsp_promo_custom_image_id"
                    value="<?php echo esc_attr( $promo_image_id ); ?>" />
                <img id="rsp_promo_custom_image_preview" src="<?php echo esc_url( $promo_image_url ); ?>"
                    style="max-width: 200px; height: auto; border:1px solid #ddd; padding:5px; margin-bottom:10px; display: <?php echo $promo_image_id ? 'block' : 'none'; ?>;" />
                <button type="button" class="button"
                    id="rsp_promo_custom_image_upload_button"><?php _e( 'Pilih/Unggah Gambar', 'referral-store-promo' ); ?></button>
                <button type="button" class="button" id="rsp_promo_custom_image_remove_button"
                    style="display: <?php echo $promo_image_id ? 'inline-block' : 'none'; ?>; margin-left:5px;"><?php _e( 'Hapus Gambar', 'referral-store-promo' ); ?></button>
            </div>
            <p class="description">
                <?php _e( 'Upload gambar product promo. Jika kosong, banner promo utama (Featured Image) akan digunakan jika ada.', 'referral-store-promo' ); ?>
            </p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">
            <label for="rsp_promo_start_date"><?php _e( 'Periode Mulai', 'referral-store-promo' ); ?></label>
        </th>
        <td>
            <input type="date" id="rsp_promo_start_date" name="rsp_promo_start_date"
                value="<?php echo esc_attr( $start_date ); ?>" class="regular-text" placeholder="YYYY-MM-DD" />
            <p class="description"><?php _e( 'Format: YYYY-MM-DD. Contoh: 2024-12-31', 'referral-store-promo' ); ?></p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">
            <label for="rsp_promo_end_date"><?php _e( 'Periode Berakhir', 'referral-store-promo' ); ?></label>
        </th>
        <td>
            <input type="date" id="rsp_promo_end_date" name="rsp_promo_end_date"
                value="<?php echo esc_attr( $end_date ); ?>" class="regular-text" placeholder="YYYY-MM-DD" />
            <p class="description"><?php _e( 'Format: YYYY-MM-DD. Contoh: 2025-01-31', 'referral-store-promo' ); ?></p>
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">
            <label for="rsp_promo_terms_conditions"><?php _e( 'Syarat & Ketentuan', 'referral-store-promo' ); ?></label>
        </th>
        <td>
            <?php
                wp_editor( $terms_conditions, 'rsp_promo_terms_conditions', array(
                    'textarea_name' => 'rsp_promo_terms_conditions',
                    'media_buttons' => false,
                    'textarea_rows' => 10,
                    'teeny'         => true,
                ) );
                ?>
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
<script type="text/javascript">
jQuery(document).ready(function($) {
    var mediaUploader;
    $('#rsp_promo_custom_image_upload_button').on('click', function(e) {
        e.preventDefault();
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: '<?php echo esc_js( __( "Pilih Gambar Promo Custom", "referral-store-promo" ) ); ?>',
            button: {
                text: '<?php echo esc_js( __( "Gunakan gambar ini", "referral-store-promo" ) ); ?>'
            },
            multiple: false
        });
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#rsp_promo_custom_image_id').val(attachment.id);
            var imageUrl = attachment.sizes.medium ? attachment.sizes.medium.url : attachment
                .url;
            $('#rsp_promo_custom_image_preview').attr('src', imageUrl).css('display', 'block');
            $('#rsp_promo_custom_image_remove_button').css('display', 'inline-block');
        });
        mediaUploader.open();
    });
    $('#rsp_promo_custom_image_remove_button').on('click', function(e) {
        e.preventDefault();
        $('#rsp_promo_custom_image_id').val('');
        $('#rsp_promo_custom_image_preview').attr('src', '').css('display', 'none');
        $(this).css('display', 'none');
    });
});
</script>
<?php
}

function rsp_save_promo_details_meta_data( $post_id ) {
    if ( ! isset( $_POST['rsp_promo_details_nonce'] ) || ! wp_verify_nonce( $_POST['rsp_promo_details_nonce'], 'rsp_save_promo_details_meta_data_action' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! isset( $_POST['post_type'] ) || RSP_PLUGIN_SLUG != $_POST['post_type'] ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( isset( $_POST['rsp_promo_short_description'] ) ) {
        update_post_meta( $post_id, '_rsp_promo_short_description', sanitize_textarea_field( $_POST['rsp_promo_short_description'] ) );
    }
    if ( isset( $_POST['rsp_promo_description'] ) ) {
        update_post_meta( $post_id, '_rsp_promo_description', sanitize_textarea_field( $_POST['rsp_promo_description'] ) );
    }
    if ( isset( $_POST['rsp_promo_start_date'] ) ) {
        update_post_meta( $post_id, '_rsp_promo_start_date', sanitize_text_field( $_POST['rsp_promo_start_date'] ) );
    }
    if ( isset( $_POST['rsp_promo_end_date'] ) ) {
        update_post_meta( $post_id, '_rsp_promo_end_date', sanitize_text_field( $_POST['rsp_promo_end_date'] ) );
    }
    if ( isset( $_POST['rsp_promo_terms_conditions'] ) ) {
        update_post_meta( $post_id, '_rsp_promo_terms_conditions', wp_kses_post( $_POST['rsp_promo_terms_conditions'] ) );
    }
    if ( isset( $_POST['rsp_promo_link'] ) ) {
        update_post_meta( $post_id, '_rsp_promo_link', esc_url_raw( $_POST['rsp_promo_link'] ) );
    }
    if ( isset( $_POST['rsp_promo_custom_image_id'] ) ) {
        $image_id = sanitize_text_field( $_POST['rsp_promo_custom_image_id'] );
        if ( !empty( $image_id ) && is_numeric( $image_id ) ) {
            update_post_meta( $post_id, '_rsp_promo_custom_image_id', absint( $image_id ) );
        } else {
            delete_post_meta( $post_id, '_rsp_promo_custom_image_id' );
        }
    } else {
        delete_post_meta( $post_id, '_rsp_promo_custom_image_id' );
    }
}
add_action( 'save_post_' . RSP_PLUGIN_SLUG, 'rsp_save_promo_details_meta_data' );

// --- KODE UNTUK HALAMAN KLAIM VIRTUAL ---
function rsp_claim_register_query_vars( $vars ) {
    $vars[] = 'rsp_promo_identifier';
    return $vars;
}
add_filter( 'query_vars', 'rsp_claim_register_query_vars' );

function rsp_claim_rewrite_rules() {
    add_rewrite_rule(
        '^klaim-promo/([^/]+)/?$',
        'index.php?rsp_promo_identifier=$matches[1]',
        'top'
    );
}
add_action( 'init', 'rsp_claim_rewrite_rules' );

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
            $args_query['name'] = $sanitized_identifier;
        }
        $promo_query = new WP_Query( $args_query );
        if ( $promo_query->have_posts() ) {
            $promo_query->the_post();
            $promo_post_obj = $GLOBALS['post'];
        }

        if ( ! $promo_post_obj ) {
            global $wp_query;
            $wp_query->set_404();
            status_header( 404 );
            get_template_part( 404 );
            exit;
        }

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

        status_header( 200 );
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

// Ini adalah fungsi AJAX handler yang PERTAMA (untuk validasi kode store)
// Pastikan tidak ada duplikasi fungsi ini di bawah.
if (!function_exists('get_doran_lokasi_data_from_api_rsp')) {
    function get_doran_lokasi_data_from_api_rsp( $id_lokasi ) {
        $api_key  = 'doran_data'; // Ambil dari options jika memungkinkan, jangan hardcode
        $base_url = 'https://kasir.doran.id/api/transaction/lokasi_pick_up?X-API-KEY=doran_data';

        $request_url = add_query_arg(
            array(
                'id'  => intval( $id_lokasi ),
            ),
            $base_url
        );

        $args = array(
            'timeout'   => 15,
            'sslverify' => true, // PERHATIAN: Untuk produksi, idealnya true jika sertifikat SSL API valid.
        );
        $response = wp_remote_get( $request_url, $args );

        if ( is_wp_error( $response ) ) {
            error_log( '[RSP Plugin] WP Remote Get Error: ' . $response->get_error_message() );
            return false;
        }

        $http_code = wp_remote_retrieve_response_code( $response );
        $body      = wp_remote_retrieve_body( $response );
        // error_log('[RSP DEBUG] Raw Body dari API Doran: ' . $body); // Uncomment untuk debugging

        if ( $http_code === 200 && ! empty( $body ) ) {
            $data = json_decode( $body, true );
            if ( json_last_error() === JSON_ERROR_NONE ) {
                // PENTING: Verifikasi key 'nama' vs 'nama_lokasi' dengan output API Anda.
                // Jika API mengembalikan 'nama_lokasi', ganti 'nama' di bawah ini menjadi 'nama_lokasi'.
                if ( isset( $data['status'] ) && isset( $data['data']) ) {
                    return $data['data']; // Mengembalikan array data toko
                } else {
                    error_log( '[RSP Plugin] API Doran Kasir Response: Unexpected structure or status not success. Body: ' . $body );
                    return false;
                }
            } else {
                error_log( '[RSP Plugin] API Doran Kasir JSON Decode Error: ' . json_last_error_msg() . '. Body: ' . $body );
                return false;
            }
        } else {
            error_log( "[RSP Plugin] API Doran Kasir Request Failed. HTTP Code: $http_code. Body: " . $body );
            return false;
        }
    }
}

// Ini adalah fungsi AJAX handler yang PERTAMA (untuk validasi kode store)
// Pastikan tidak ada duplikasi fungsi ini di bawah.
if (!function_exists('rsp_handle_validate_store_code')) {
    add_action( 'wp_ajax_rsp_validate_store_code', 'rsp_handle_validate_store_code' );
    add_action( 'wp_ajax_nopriv_rsp_validate_store_code', 'rsp_handle_validate_store_code' );

    function rsp_handle_validate_store_code() {
        // Verifikasi Nonce - Action name HARUS SAMA dengan yang dibuat di wp_create_nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'rsp_claim_promo_nonce' ) ) { // DIPERBAIKI: Action name nonce disamakan
            wp_send_json_error( array( 'message' => 'Validasi keamanan gagal. Silakan muat ulang halaman dan coba lagi.' ), 403 );
            return;
        }

        if ( ! isset( $_POST['kode_store'] ) || empty( trim( $_POST['kode_store'] ) ) ) {
            wp_send_json_error( array( 'message' => 'Kode store tidak boleh kosong.' ) );
            return;
        }

        $kode_store = sanitize_text_field( trim( $_POST['kode_store'] ) );
        // error_log('[RSP DEBUG] Kode Store Diterima AJAX: ' . $kode_store); // Uncomment untuk debugging

        $store_data = get_doran_lokasi_data_from_api_rsp( $kode_store );
        // error_log('[RSP DEBUG] Data dari API: ' . print_r($store_data, true)); // Uncomment untuk debugging

        // PENTING: Verifikasi key 'nama' vs 'nama_lokasi' dengan output API Anda.
        // Jika API mengembalikan 'nama_lokasi', ganti 'nama' di bawah ini menjadi 'nama_lokasi'.
        if ( $store_data && isset( $store_data[0]['nama'] ) ) {
            wp_send_json_success(
                array(
                    'message'    => 'Kode Store Valid: ' . esc_html( $store_data[0]['name'] ),
                    'store_name' => esc_html( $store_data[0]['name'] ),
                )
            );
        } else {
            wp_send_json_error( array( 'message' => 'KODE YANG ANDA MASUKAN TIDAK DIKENALI OLEH SISTEM. PASTIKAN KODE ANDA BENAR' ) );
        }
    }
}


// Fungsi AJAX handler untuk proses klaim server-side (contoh, mungkin tidak terpakai jika semua via webhook)
// Jika Anda tidak menggunakan ini, Anda bisa menghapusnya.
function rsp_handle_ajax_process_claim_server_side() {
    // Nonce 'rsp_claim_promo_nonce' juga dipakai di sini, pastikan JS mengirimkannya
    // dengan nama field 'security_nonce_name' (sesuai check_ajax_referer)
    check_ajax_referer( 'rsp_claim_promo_nonce', 'security_nonce_name' );

    $kode_store = isset($_POST['kode_store']) ? sanitize_text_field($_POST['kode_store']) : '';
    $promo_slug = isset($_POST['promo_slug']) ? sanitize_text_field($_POST['promo_slug']) : '';
    $whatsapp = isset($_POST['whatsapp']) ? sanitize_text_field($_POST['whatsapp']) : '';

    $kode_store = sanitize_text_field( trim( $_POST['kode_store'] ) );
        // error_log('[RSP DEBUG] Kode Store Diterima AJAX: ' . $kode_store); // Uncomment untuk debugging

    $store_data = get_doran_lokasi_data_from_api_rsp( $kode_store );

    if ( empty($kode_store) || empty($promo_slug) ) {
        wp_send_json_error(array('message' => __('Data tidak lengkap.', 'referral-store-promo')));
        return;
    }

    $promo_post = get_page_by_path($promo_slug, OBJECT, RSP_PLUGIN_SLUG);

    

    if ($result === false) {
        wp_send_json_error('Gagal menyimpan data klaim.');
    }

    wp_send_json_success('Berhasil disimpan.');

    if ( ! $promo_post || $promo_post->post_status !== 'publish' ) {
        wp_send_json_error(array('message' => __('Promo tidak valid.', 'referral-store-promo')));
        return;
    }
    // Logika validasi atau penyimpanan data klaim ke database WordPress Anda bisa ditambahkan di sini
    wp_send_json_success(array('message' => __('Validasi server berhasil (contoh).', 'referral-store-promo')));
}
add_action( 'wp_ajax_rsp_process_server_claim', 'rsp_handle_ajax_process_claim_server_side' );
add_action( 'wp_ajax_nopriv_rsp_process_server_claim', 'rsp_handle_ajax_process_claim_server_side' );


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

function rsp_load_textdomain_plugin() {
    load_plugin_textdomain( 'referral-store-promo', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'rsp_load_textdomain_plugin' );

function rsp_include_custom_cpt_template( $template ) {
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

// Catatan: Pastikan TIDAK ADA duplikasi fungsi get_doran_lokasi_data_from_api_rsp()
// dan rsp_handle_validate_store_code() (beserta add_action-nya) di bawah ini.
// Duplikasi sudah dihapus dari versi ini.

?>