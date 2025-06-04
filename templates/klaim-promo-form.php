<?php
/**
 * Template untuk Halaman Form Klaim Promo Virtual (dengan Dial Pad).
 * Variabel global $rsp_claim_form_data akan berisi data promo.
 */


global $rsp_claim_form_data;
global $post; // $post di-set oleh rsp_claim_template_redirect

$promo_obj        = isset($rsp_claim_form_data['promo_obj']) ? $rsp_claim_form_data['promo_obj'] : null;
$terms_conditions = '';
if (is_object($promo_obj) && isset($promo_obj->ID)) {
    $terms_conditions = get_post_meta( $promo_obj->ID, '_rsp_promo_terms_conditions', true );
}

// Siapkan variabel PHP yang akan dicetak ke JavaScript
$js_ajax_url = admin_url('admin-ajax.php');
$js_nonce    = wp_create_nonce('rsp_claim_promo_nonce'); // Action name SAMA dengan yang diverifikasi

$js_kode_promo_val = '';
if (is_object($promo_obj) && property_exists($promo_obj, 'post_name')) {
    $js_kode_promo_val = $promo_obj->post_name;
}

// Ambil string terjemahan jika ada (dari $dial_script_vars di plugin utama, jika Anda ingin tetap konsisten)
// Ini contoh, Anda mungkin perlu mengambilnya dari array $dial_script_vars jika sudah dibuat sebelumnya
// atau definisikan di sini. Untuk kesederhanaan, saya hardcode beberapa.
$js_error_kode_store_empty = __('Kode Store tidak boleh kosong.', 'referral-store-promo');
$js_submitting_text = __('...', 'referral-store-promo');
$js_validating_text = __('...', 'referral-store-promo');
$js_webhook_url = 'https://services.leadconnectorhq.com/hooks/EBB7zornJZkBodHpGN3B/webhook-trigger/fd58e12a-3741-4b67-a6b5-81ae48981be2'; // Sebaiknya dari options
$js_thankyou_page_url = site_url('/thank-you/');


get_header();
?>
<div id="overlay" class="hide">
    <div class="overlay-content">
        <p class="overlay-title">Syarat dan Ketentuan</p>
        <p><?php echo wp_kses_post( $terms_conditions ); ?></p>
        <button class="overlay-close">Tutup</button>
    </div>
</div>
<div id="primary" class="content-area rsp-claim-form-page">
    <div class="referral-input-section">
        <div class="headerr">
            <button onclick="window.history.back()" class="backBtn">Back</button>
            <img class="jete-logo"
                src="<?php echo esc_url( defined('RSP_PLUGIN_URL') ? RSP_PLUGIN_URL . 'assets/images/logo.png' : '' ); ?>"
                alt="<?php esc_attr_e( 'JETE Indonesia', 'referral-store-promo' ); ?>">
        </div>
        <div class="dial-pad-container">
            <div class="dial-head">
                <p class="heading">Masukan Kode Kasir</p>
                <p class="desc">Silakan lakukan validasi kode melalui kode kasir di <span class="orange-text">
                        Official Store JETE & DORAN GADGET </span> terdekatmu
                </p>
            </div>
            <div id="rspClaimAjaxResult" class="rsp-claim-ajax-result" style="display:none;"></div>
            <div class="dial-pad-display-area">
                <input type="text" id="phoneNumberDisplay" readonly placeholder="Kode Kasir">
            </div>
            <div class="dial-pad">
                <button type="button" class="dial-button" data-value="1">1</button>
                <button type="button" class="dial-button" data-value="2">2</button>
                <button type="button" class="dial-button" data-value="3">3</button>
                <button type="button" class="dial-button" data-value="4">4</button>
                <button type="button" class="dial-button" data-value="5">5</button>
                <button type="button" class="dial-button" data-value="6">6</button>
                <button type="button" class="dial-button" data-value="7">7</button>
                <button type="button" class="dial-button" data-value="8">8</button>
                <button type="button" class="dial-button" data-value="9">9</button>
                <button type="button" id="deleteButton" class="dial-action-button delete-button">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24"
                        height="24">
                        <path
                            d="M22 3H7c-.69 0-1.23.35-1.59.88L0 12l5.41 8.12c.36.53.9.88 1.59.88h15c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-3.71 13.29a.9959.9959 0 0 1-1.41 0L14 13.41l-2.88 2.88a.9959.9959 0 0 1-1.41 0c-.39-.39-.39-1.02 0-1.41L12.59 12 9.71 9.12c-.39-.39-.39-1.02 0-1.41s1.02-.39 1.41 0L14 10.59l2.88-2.88c.39-.39 1.02-.39 1.41 0s.39 1.02 0 1.41L15.41 12l2.88 2.88c.39.39.39 1.03 0 1.41z" />
                    </svg>
                </button>
                <button type="button" class="dial-button" data-value="0">0</button>
                <button type="button" id="submitButton" class="dial-action-button call-button">✔</button>
            </div>
            <p class="sk">Syarat & Ketentuan Promo</p>
        </div>
    </div>
    </main>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const display = document.getElementById('phoneNumberDisplay');
    const submitDialButton = document.getElementById('submitButton');
    const headingElement = document.querySelector('.dial-head p.heading');
    const descElement = document.querySelector('.dial-head p.desc');
    const ajaxResultDiv = document.getElementById('rspClaimAjaxResult');
    let skBtn = document.querySelector('.sk');
    let ovrlay = document.querySelector('#overlay');
    let close = document.querySelector('.overlay-close');
    let bodyy = document.querySelector('body');

    // Variabel yang dicetak langsung dari PHP ke JavaScript
    const VJS_AJAX_URL = '<?php echo esc_js($js_ajax_url); ?>';
    const VJS_NONCE =
        '<?php echo esc_js($js_nonce); ?>'; // Nonce sudah dibuat dengan action 'rsp_claim_promo_nonce'
    const VJS_KODE_PROMO = <?php echo json_encode($js_kode_promo_val); ?>;
    const VJS_ERROR_KODE_STORE_EMPTY = '<?php echo esc_js($js_error_kode_store_empty); ?>';
    const VJS_SUBMITTING_TEXT = '<?php echo esc_js($js_submitting_text); ?>';
    const VJS_VALIDATING_TEXT = '<?php echo esc_js($js_validating_text); ?>';
    const VJS_WEBHOOK_URL = '<?php echo esc_js($js_webhook_url); ?>';
    const VJS_THANKYOU_PAGE_URL = '<?php echo esc_js($js_thankyou_page_url); ?>';


    skBtn.addEventListener('click', () => {
        ovrlay.classList.toggle("hide");
        ovrlay.classList.toggle("show");
        bodyy.style.overflow = 'hidden';
    });
    close.addEventListener('click', () => {
        ovrlay.classList.toggle("hide");
        ovrlay.classList.toggle("show");
        bodyy.style.overflow = 'auto';
    });

    if (!display || !submitDialButton || !headingElement || !descElement) {
        /* ... */
        return;
    }




    let currentInputStep = 'kodeStore';
    let collectedKodeStore = '';
    let storeNameValidated = '';

    const jsKodePromo = VJS_KODE_PROMO; // Menggunakan variabel yang sudah disiapkan




    function updateDialPadUI(step) {
        /* ... sama ... */

        display.value = '';
        if (step === 'kodeStore') {
            headingElement.textContent = 'Masukan Kode Kasir';
            descElement.innerHTML =
                'Silakan lakukan validasi kode melalui kode kasir di <span class="orange-text">Official Store JETE & DORAN GADGET</span> terdekatmu';
            display.placeholder = 'Kode Kasir';
            if (descElement.classList.contains("waDesc")) descElement.classList.remove("waDesc");
        } else if (step === 'whatsapp') {
            headingElement.textContent = 'Masukan Nomor WA';
            descElement.textContent = 'Silakan masukan nomor Telf yang terhubung ke Whatsapp';
            display.placeholder = 'Nomor WhatsApp';
            descElement.classList.add("waDesc");
        }
    }
    updateDialPadUI(currentInputStep);

    const maxDigits = 15;
    const dialButtons = document.querySelectorAll('.dial-pad .dial-button');
    const deleteDialPadButton = document.getElementById('deleteButton');
    dialButtons.forEach(button => {
        /* ... sama ... */
        button.addEventListener('click', function() {
            const value = this.dataset.value;
            if (display.value.length < maxDigits) {
                display.value += value;
            }
        });
    });
    if (deleteDialPadButton) {
        /* ... sama ... */
        deleteDialPadButton.addEventListener('click', function() {
            display.value = display.value.slice(0, -1);
        });
    }



    if (submitDialButton) {
        submitDialButton.addEventListener('click', async function() {
            const currentDisplayValue = display.value.trim();
            const originalButtonHtml = this.innerHTML;

            if (ajaxResultDiv) {
                /* ... reset ... */
            }

            if (currentInputStep === 'kodeStore') {
                if (currentDisplayValue.length === 0) {
                    if (ajaxResultDiv) {
                        ajaxResultDiv.textContent = VJS_ERROR_KODE_STORE_EMPTY;
                        ajaxResultDiv.classList.add('rsp-error');
                        ajaxResultDiv.style.display = 'block';
                    } else {
                        alert(VJS_ERROR_KODE_STORE_EMPTY);
                    }
                    return;
                }

                this.disabled = true;
                this.innerHTML = VJS_VALIDATING_TEXT;

                const formData = new FormData();
                formData.append('action', 'rsp_validate_store_code');
                formData.append('kode_store', currentDisplayValue);
                formData.append('nonce', VJS_NONCE); // Menggunakan VJS_NONCE

                if (!VJS_NONCE) {
                    console.error('ERROR: Nilai Nonce kosong di VJS_NONCE!');
                    // ... (tampilkan error ke user) ...
                    this.disabled = false;
                    this.innerHTML = originalButtonHtml;
                    return;
                }

                fetch(VJS_AJAX_URL, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        /* ... sama ... */
                        if (!response.ok) {
                            return response.json().catch(() => response.text()).then(
                                errData => {
                                    let errMsg =
                                        `Error ${response.status}: ${response.statusText}`;
                                    if (typeof errData === 'object' && errData.data &&
                                        errData
                                        .data.message) {
                                        errMsg = errData.data.message;
                                    } else if (typeof errData === 'string' && errData
                                        .length >
                                        0 && errData.length < 200) {
                                        errMsg = errData;
                                    }
                                    throw new Error(errMsg);
                                });
                        }
                        return response.json();
                    })
                    .then(result => {
                        /* ... sama ... */
                        this.disabled = false;
                        this.innerHTML = originalButtonHtml;
                        if (result.success) {
                            collectedKodeStore = currentDisplayValue;

                            storeNameValidated = result.data.store_name || '';
                            currentInputStep = 'whatsapp';
                            updateDialPadUI(currentInputStep);
                            if (ajaxResultDiv) {
                                ajaxResultDiv.textContent = result.data.message ||
                                    'Kode Store Valid.';
                                ajaxResultDiv.classList.remove('rsp-error');
                                ajaxResultDiv.classList.add('rsp-success');
                                ajaxResultDiv.style.display = 'block';

                                setTimeout(() => {
                                    if (ajaxResultDiv.classList.contains('rsp-success'))
                                        ajaxResultDiv.style.display = 'none';
                                }, 1000);
                            }
                        } else {
                            if (ajaxResultDiv) {
                                ajaxResultDiv.textContent = (result.data && result.data
                                        .message) ?
                                    result.data.message : 'Kode Store tidak valid.';
                                ajaxResultDiv.classList.add('rsp-error');
                                ajaxResultDiv.style.display = 'block';
                            } else {
                                alert((result.data && result.data.message) ? result.data
                                    .message :
                                    'Kode Store tidak valid.');
                            }
                        }
                    })
                    .catch(error => {
                        /* ... sama ... */
                        console.error('Error saat validasi kode store:', error);
                        this.disabled = false;
                        this.innerHTML = originalButtonHtml;
                        if (ajaxResultDiv) {
                            ajaxResultDiv.textContent = 'Terjadi kesalahan: ' + error.message +
                                '. Mohon coba lagi.';
                            ajaxResultDiv.classList.add('rsp-error');
                            ajaxResultDiv.style.display = 'block';
                        } else {
                            alert('Terjadi kesalahan: ' + error.message + '. Mohon coba lagi.');
                        }
                    });

            } else if (currentInputStep === 'whatsapp') {
                // ... (logika submit ke webhook, menggunakan VJS_WEBHOOK_URL, VJS_THANKYOU_PAGE_URL, VJS_SUBMITTING_TEXT)
                const noWhatsapp = currentDisplayValue;
                if (noWhatsapp.length === 0 || !/^\d{8,15}$/.test(noWhatsapp)) {
                    if (ajaxResultDiv) {

                        ajaxResultDiv.textContent = 'Format Nomor WhatsApp tidak valid.';

                        ajaxResultDiv.classList.add('rsp-error');

                        ajaxResultDiv.style.display = 'block';

                    } else {

                        alert('Format Nomor WhatsApp tidak valid.');

                    }
                    return;
                }
                if (!jsKodePromo) {
                    console.error('Kode Promo tidak ditemukan untuk submit akhir.');

                    if (ajaxResultDiv) {

                        ajaxResultDiv.textContent =

                            'Error: Kode Promo tidak ditemukan. Tidak bisa mengirim data.';

                        ajaxResultDiv.classList.add('rsp-error');

                        ajaxResultDiv.style.display = 'block';

                    } else {

                        alert('Error: Kode Promo tidak ditemukan. Tidak bisa mengirim data.');

                    }


                    return;
                }

                async function fetchClaimById(id) {
                    try {
                        const response = await fetch(
                            `https://kasir.doran.id/api/transaction/lokasi_pick_up?X-API-KEY=doran_data&id=${encodeURIComponent(id)}`
                        );

                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }

                        const data = await response.json();

                        // Perbaikan di sini
                        const item = data.data.find(obj => obj.kode === Number(id));

                        return item ? item.nama_display : 'Kosong';

                    } catch (error) {
                        console.error('Fetch claim by ID failed:', error);
                        return null;
                    }
                }





                fetch(VJS_AJAX_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            action: 'rsp_claim_promo',
                            nonce: VJS_NONCE,
                            nama_store: await fetchClaimById(collectedKodeStore),
                            kode_store: collectedKodeStore,
                            nomor_wa: noWhatsapp,
                            kode_promo: VJS_KODE_PROMO
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = VJS_THANKYOU_PAGE_URL;
                        } else {
                            alert(data.data);
                        }
                    });

                this.disabled = true;
                this.innerHTML = VJS_SUBMITTING_TEXT;
                const dataToSend = {
                    "whatsapp": noWhatsapp,

                    "kode_store": collectedKodeStore,

                    "promo": jsKodePromo // Pastikan jsKodePromo berisi nilai kode promo yang benar
                };

                fetch(VJS_WEBHOOK_URL, {
                        method: 'POST',

                        headers: {

                            'Content-Type': 'application/json',

                        },

                        body: JSON.stringify(dataToSend)
                    })
                    .then(response => {
                        // LeadConnector biasanya mengembalikan 200 OK untuk sukses, bahkan jika body-nya hanya teks

                        if (response.ok) {

                            return response

                                .text(); // Atau response.json() jika webhook mengembalikan JSON

                        } else {

                            // Coba baca error dari body jika ada, fallback ke statusText

                            return response.text().then(text => {

                                throw new Error(

                                    `Webhook Gagal: ${response.status} ${response.statusText} - Pesan: ${text || 'Tidak ada pesan tambahan'}`

                                );

                            });

                        }
                    })
                    .then(data => {
                        let successMessage = "Voucher Berhasil di Klaim";

                        let isSuccess = true;



                        // Anda mungkin perlu menyesuaikan cara Anda menentukan kesuksesan berdasarkan respons aktual webhook

                        // Misalnya, jika webhook mengembalikan JSON: if (data.success === true) { ... }

                        if (typeof data === 'string' && data.toLowerCase().includes(

                                'error'

                            )) { // Contoh sederhana jika response text mengandung "error"

                            isSuccess = false;

                            successMessage = `Pesan dari webhook: ${data}`;

                        } else if (typeof data === 'object' && data !== null && data.success ===

                            false) {

                            isSuccess = false;

                            successMessage = data.message ||

                                `Pesan dari webhook: ${JSON.stringify(data)}`;

                        } else if (typeof data === 'object' && data !== null && data.message) {

                            successMessage = data.message; // Jika ada pesan spesifik

                        }





                        if (ajaxResultDiv) {

                            ajaxResultDiv.innerHTML = successMessage;

                            ajaxResultDiv.classList.remove('rsp-error', 'rsp-success');

                            ajaxResultDiv.classList.add(isSuccess ? 'rsp-success' :
                                'rsp-error');

                            ajaxResultDiv.style.display = 'block';

                        } else {

                            alert(successMessage);

                        }
                        if (isSuccess) {
                            setTimeout(() => {
                                if (VJS_THANKYOU_PAGE_URL) window.location.href =
                                    VJS_THANKYOU_PAGE_URL;
                            }, 1000);
                        }
                    })
                    .catch(error => {
                        console.error('Webhook Fetch Error:', error);

                        if (ajaxResultDiv) {

                            ajaxResultDiv.textContent = 'Gagal mengirim data: ' + error.message;

                            ajaxResultDiv.classList.remove('rsp-success');

                            ajaxResultDiv.classList.add('rsp-error');

                            ajaxResultDiv.style.display = 'block';

                        } else {

                            alert('Gagal mengirim data: ' + error.message);

                        }
                    })
                    .finally(() => {
                        this.disabled = false;

                        this.innerHTML = originalButtonText;
                    });
            }
        });
    }




    // Pengecekan akhir (opsional, karena kita mencetak langsung dari PHP)
    if (!VJS_AJAX_URL || !VJS_NONCE) {
        console.warn(
            'Salah satu dari VJS_AJAX_URL atau VJS_NONCE tidak memiliki nilai. Periksa blok PHP di atas script ini.'
        );
        // Tampilkan error ke pengguna jika perlu
    } else {
        console.log('Parameter JS untuk AJAX (VJS_AJAX_URL, VJS_NONCE) berhasil diinisialisasi.');
        console.log('Nonce yang akan dikirim: ', VJS_NONCE);
    }
});
</script>
<?php
// get_footer();
?>