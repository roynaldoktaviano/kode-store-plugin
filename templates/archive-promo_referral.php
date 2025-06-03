<?php
/**
 * Template untuk menampilkan arsip Promo Referral.
 * File ini ada di dalam: wp-content/plugins/referral-store-promo/templates/archive-promo_referral.php
 * 
 */

get_header(); // Memuat header tema
?>

<div id="primary " class="content-area">
    <main id="main archive-voucher" class="site-main" role="main">

        <?php if ( have_posts() ) : ?>

        <header class="page-header archive-header">
            <img class="jete-logo" src="<?php echo esc_url( RSP_PLUGIN_URL . 'assets/images/logo.png' ); ?>"
                alt="<?php esc_attr_e( 'JETE Indonesia', 'referral-store-promo' ); ?>">
        </header>
        <div class="promo-referral-archive-list promo-container">
            <?php
            // Memulai Loop WordPress untuk menampilkan setiap promo
            while ( have_posts() ) :
                the_post();
                // Ambil data meta yang mungkin ingin ditampilkan di arsip
                $short_description = get_post_meta( get_the_ID(), '_rsp_promo_short_description', true );
                $start_date        = get_post_meta( get_the_ID(), '_rsp_promo_start_date', true );
                $end_date          = get_post_meta( get_the_ID(), '_rsp_promo_end_date', true );
            ?>
            <article id="post-<?php the_ID(); ?>"
                <?php post_class( 'promo-referral-archive-item' ); // Tambahkan class CSS khusus ?>>

                <?php if ( has_post_thumbnail() ) : // Cek jika ada Banner Promo ?>
                <div class="promo-archive-banner">
                    <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                        <?php the_post_thumbnail( 'full' ); // Tampilkan banner, 'medium' atau 'thumbnail' cocok untuk arsip ?>
                    </a>
                </div>
                <?php endif; ?>
                <img src="<?php echo esc_url( RSP_PLUGIN_URL . 'assets/images/list.png' ); ?>"
                    alt="<?php esc_attr_e( 'Ikon Spesial', 'referral-store-promo' ); ?>">
                <div class="promo-archive-content">

                    <header class="entry-header">
                        <?php the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>
                    </header><?php if ( ! empty( $short_description ) ) : ?>
                    <div class="promo-archive-short-description">
                        <p><?php echo esc_html( wp_trim_words( $short_description, 20, '...' ) ); // Ambil 20 kata pertama ?>
                        </p>
                    </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $start_date ) || ! empty( $end_date ) ) : ?>
                    <div class="promo-archive-period">
                        <p>

                            <?php if ( ! empty( $start_date ) && ! empty( $end_date ) ) : echo ' '; endif; ?>
                            <?php if ( ! empty( $end_date ) ) : ?>
                            <small><?php echo esc_html_e( 'Valid until', 'referral-store-promo' ) . ' ' . esc_html( $end_date ); ?></small>
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php endif; ?>


                </div>
            </article><?php
            endwhile;
            ?>
        </div><?php

            // Navigasi halaman (Pagination)
            the_posts_pagination( array(
                'prev_text'          => esc_html__( 'Sebelumnya', 'referral-store-promo' ),
                'next_text'          => esc_html__( 'Berikutnya', 'referral-store-promo' ),
                'before_page_number' => '<span class="meta-nav screen-reader-text">' . esc_html__( 'Halaman', 'referral-store-promo' ) . ' </span>',
            ) );

        else :
            // Jika tidak ada promo yang ditemukan
            ?>
        <section class="no-results not-found">
            <header class="page-header">
                <h1 class="page-title"><?php esc_html_e( 'Tidak Ada Promo Ditemukan', 'referral-store-promo' ); ?></h1>
            </header>
            <div class="page-content">
                <p><?php esc_html_e( 'Maaf, saat ini belum ada promo referral yang tersedia. Silakan cek kembali nanti.', 'referral-store-promo' ); ?>
                </p>
            </div>
        </section>
        <?php
        endif;
        wp_reset_postdata(); // Penting untuk mereset query post
        ?>

    </main>
</div>