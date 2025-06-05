<?php
/**
 * Template untuk menampilkan single Promo Referral.
 * File ini ada di dalam: wp-content/plugins/referral-store-promo/templates/single-promo_referral.php
 */

 $terms_conditions  = get_post_meta( $post->ID, '_rsp_promo_terms_conditions', true );
 $promo_description  = get_post_meta( $post->ID, '_rsp_promo_description', true );

get_header(); // Memuat header tema
?>
<div id="overlay-desc" class="hide">
    <div class="overlay-content">
        <p class="overlay-title">Promo Detail Description</p>
        <div class="prom-desc">
            <?echo  wpautop($promo_description)  ?>
        </div>


        <button class="overlay-close-desc">Tutup</button>
    </div>
</div>
<div id="overlay" class="hide">
    <div class="overlay-content">
        <p class="overlay-title">Syarat dan Ketentuan</p>
        <p>
            <?echo  $terms_conditions  ?>

        </p>
        <button class="overlay-close">Tutup</button>
    </div>
</div>
<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">

        <?php
        // Memulai Loop WordPress
        if ( have_posts() ) :
            while ( have_posts() ) : the_post();

                // Ambil data meta yang sudah disimpan
                $short_description = get_post_meta( get_the_ID(), '_rsp_promo_short_description', true );
                $start_date        = get_post_meta( get_the_ID(), '_rsp_promo_start_date', true );
                $end_date          = get_post_meta( get_the_ID(), '_rsp_promo_end_date', true );
                $terms_conditions  = get_post_meta( get_the_ID(), '_rsp_promo_terms_conditions', true );
                $promo_link        = get_post_meta( get_the_ID(), '_rsp_promo_link', true );
                $promo_image_id = get_post_meta( $post->ID, '_rsp_promo_custom_image_id', true );
                 $promo_image_url = wp_get_attachment_image_url( $promo_image_id, 'full' );
        ?>

        <article id="post-<?php the_ID(); ?>"
            <?php post_class( 'promo-referral-single' ); // Tambahkan class CSS khusus ?>>
            <header class="entry-header single-promo-head">
                <div>
                    <button onclick="window.location.href='/promo_referral'" class="backBtn">Promo Lainnya</a>
                </div>
                <img class="jete-logo" src="<?php echo esc_url( RSP_PLUGIN_URL . 'assets/images/logo.png' ); ?>"
                    alt="<?php esc_attr_e( 'JETE Indonesia', 'referral-store-promo' ); ?>">

            </header>

            <div class="entry-content promo-details">
                <div class="promo-banner">
                    <?php the_post_thumbnail( 'full' ); ?>

                </div>

                <div class="promo-body">
                    <?php if ( ! empty( $short_description ) ) : ?>
                    <div class="promo-short-description">
                        <div class="title-promo-container">
                            <h2 class="entry-title-detail">
                                <?php the_title( sprintf( '<p class="promo-title">', esc_url( get_permalink() ) ), '</p>' ); ?>
                            </h2>
                            <p class="see-detail-promo">Lihat Detail</p>
                        </div>
                        <p class="short_desc_detail"><?php echo nl2br( esc_html( $short_description ) ); ?></p>
                    </div>
                    <?php endif; ?>

                    <div class='cards'>

                        <div class='card days'>
                            <div class='flip-card'>
                                <div class='top-half'>00</div>
                                <div class='bottom-half'>00</div>
                            </div>
                            <p>Days</p>
                        </div>

                        <div class='card hours'>
                            <div class='flip-card'>
                                <div class='top-half'>00</div>
                                <div class='bottom-half'>00</div>
                            </div>
                            <p>Hours</p>
                        </div>

                        <div class='card minutes'>
                            <div class='flip-card'>
                                <div class='top-half'>00</div>
                                <div class='bottom-half'>00</div>
                            </div>
                            <p>Minutes</p>
                        </div>

                        <div class='card seconds'>
                            <div class='flip-card'>
                                <div class='top-half'>00</div>
                                <div class='bottom-half'>00</div>
                            </div>
                            <p>Seconds</p>
                        </div>

                    </div>

                    <img class="list-detail" src="<?php echo esc_url( RSP_PLUGIN_URL . 'assets/images/list2.png' ); ?>"
                        alt="<?php esc_attr_e( 'Ikon Spesial', 'referral-store-promo' ); ?>">
                    <div>
                        <p class="text-explain">Tukarkan kupon digital ini di <span class="txt-orange"> Official Store
                                JETE & DORAN GADGET</span>
                            terdekatmu </p>
                    </div>
                    <div class="locContainer">
                        <a href="https://jete.id/store" target="_blank" style="" class="locLink">
                            <img style="object-fit:contain"
                                src="<?php echo esc_url( RSP_PLUGIN_URL . 'assets/images/loc.png' ); ?>"
                                alt="<?php esc_attr_e( 'Ikon Spesial', 'referral-store-promo' ); ?>">
                            Lokasi Penukaran
                        </a>
                    </div>

                    <?php
                    global $post;
                    $promo_identifier_for_url = $post->post_name;
                    $url_klaim_promo = home_url( '/klaim-promo/' . rawurlencode( $promo_identifier_for_url ) . '/' );    
                    ?>

                    <div class="promo-action-link">
                        <a href="<?php echo esc_url( $url_klaim_promo ); ?>" class="button promo-button">
                            <?php esc_html_e( 'KLAIM VOUCHER', 'referral-store-promo' ); ?>
                        </a>
                    </div>

                    <button class="sk">
                        Syarat & Ketentuan Promo
                    </button>


                    <?php if ( ! empty( $promo_link ) ) : ?>

                    <div class="promo-action-link">
                        <a href="<?php echo esc_url( $promo_link ); ?>" target="_blank" class="button promo-button">
                            <?php esc_html_e( 'Kunjungi Promo!', 'referral-store-promo' ); ?>
                        </a>
                    </div>
                    <?php endif; ?>

                    <hr>

                </div>

            </div>

            <footer class="entry-footer">
                <?php // Anda bisa menambahkan meta lain di sini jika perlu ?>
            </footer>
            <script>
            // Variabel global untuk tanggal hitung mundur
            var endDateFromPHP = <?php echo wp_json_encode($end_date); ?>;
            countdownDate = new Date(endDateFromPHP);

            let skBtn = document.querySelector('.sk');
            let seeDetail = document.querySelector('.see-detail-promo');
            let ovrlay = document.querySelector('#overlay');
            let ovrlayDesc = document.querySelector('#overlay-desc');
            let close = document.querySelector('.overlay-close');
            let closeDesc = document.querySelector('.overlay-close-desc');
            let bodyy = document.querySelector('body');
            skBtn.addEventListener('click', () => {
                ovrlay.classList.toggle("hide");
                ovrlay.classList.toggle("show");
                bodyy.style.overflow = 'hidden';
            })
            seeDetail.addEventListener('click', () => {
                ovrlayDesc.classList.toggle("hide");
                ovrlayDesc.classList.toggle("show");
                bodyy.style.overflow = 'hidden';
            })

            close.addEventListener('click', () => {
                ovrlay.classList.toggle("hide");
                ovrlay.classList.toggle("show");
                bodyy.style.overflow = 'auto';
            })

            closeDesc.addEventListener('click', () => {
                ovrlayDesc.classList.toggle("hide");
                ovrlayDesc.classList.toggle("show");
                bodyy.style.overflow = 'auto';
            })



            // Variabel global untuk elemen DOM
            var daysCard = document.querySelector(".days").querySelector(".flip-card");
            var hoursCard = document.querySelector(".hours").querySelector(".flip-card");
            var minutesCard = document.querySelector(".minutes").querySelector(".flip-card");
            var secondsCard = document.querySelector(".seconds").querySelector(".flip-card");

            // Fungsi untuk mendapatkan total waktu yang tersisa
            function getTimeRemaining(targetDate) {
                var now = new Date();
                var diff = targetDate - now;

                var days = Math.floor(diff / (1000 * 60 * 60 * 24));
                var hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
                var minutes = Math.floor((diff / 1000 / 60) % 60);
                var seconds = Math.floor((diff / 1000) % 60);

                return {
                    diff: diff,
                    days: days,
                    hours: hours,
                    minutes: minutes,
                    seconds: seconds
                };
            }

            // Fungsi untuk menambahkan animasi flip pada kartu
            function addFlip(cardElement, time) {
                // Pastikan waktu telah berubah
                var currentTimeText = cardElement.querySelector(".top-half").innerText;
                // Menggunakan perbandingan longgar (==) seperti pada kode asli.
                // Pertimbangkan perbandingan ketat (===) jika tipe datanya pasti.
                if (time == currentTimeText) {
                    return;
                }

                var formattedTime = time <= 9 ? "0" + time : String(time); // Pastikan string untuk konsistensi
                var topHalf = cardElement.querySelector(".top-half");
                var bottomHalf = cardElement.querySelector(".bottom-half");

                var topFlip = document.createElement("div");
                var bottomFlip = document.createElement("div");

                // Tambahkan animasi, isi dengan waktu saat ini
                topFlip.className =
                    "top-flip"; // Menggunakan className untuk kompatibilitas lebih luas daripada classList
                topFlip.innerText = currentTimeText;

                bottomFlip.className = "bottom-flip";

                // Animasi dimulai, perbarui top-half ke waktu baru
                topFlip.addEventListener("animationstart", function() {
                    topHalf.innerText = formattedTime;
                });

                // Animasi pberakhir, hapus div animasi, perbarui animasi bawah ke waktu baru
                topFlip.addEventListener("animationend", function() {
                    // Hapus elemen topFlip dari parent node-nya
                    if (topFlip.parentNode) {
                        topFlip.parentNode.removeChild(topFlip);
                    }
                    bottomFlip.innerText = formattedTime;
                });

                // Animasi berakhir, perbarui bottom-half ke waktu baru, hapus div animasi
                bottomFlip.addEventListener("animationend", function() {
                    bottomHalf.innerText = formattedTime;
                    // Hapus elemen bottomFlip dari parent node-nya
                    if (bottomFlip.parentNode) {
                        bottomFlip.parentNode.removeChild(bottomFlip);
                    }
                });

                cardElement.appendChild(topFlip);
                cardElement.appendChild(bottomFlip);
            }

            // Fungsi untuk menginisialisasi jam hitung mundur
            function initializeClock(targetDate) {
                var timeinterval; // Deklarasikan di sini agar bisa diakses oleh clearInterval

                function updateClock() {
                    var t = getTimeRemaining(targetDate);
                    addFlip(daysCard, t.days);
                    addFlip(hoursCard, t.hours);
                    addFlip(minutesCard, t.minutes);
                    addFlip(secondsCard, t.seconds);
                    console.log(targetDate)

                    if (t.diff <= 0) {
                        clearInterval(timeinterval);
                    }
                }

                updateClock(); // Panggil sekali di awal agar tidak ada jeda
                timeinterval = setInterval(updateClock, 1000);
            }

            // Mulai jam hitung mundur
            initializeClock(countdownDate);
            </script>
        </article><?php
            endwhile;
        else :
            // Jika tidak ada post yang ditemukan
            get_template_part( 'template-parts/content', 'none' ); // Atau pesan custom
        endif;
        wp_reset_postdata(); // Penting untuk mereset query post
        ?>

    </main>
</div>