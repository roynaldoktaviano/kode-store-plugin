<?php
/**
 * Template untuk Halaman Form Klaim Promo Virtual (dengan Dial Pad).
 * Variabel global $rsp_claim_form_data akan berisi data promo.
 */

global $rsp_claim_form_data;

$promo_obj       = isset($rsp_claim_form_data['promo_obj']) ? $rsp_claim_form_data['promo_obj'] : null;
$is_active       = isset($rsp_claim_form_data['is_active']) ? $rsp_claim_form_data['is_active'] : false;
$initial_message = isset($rsp_claim_form_data['initial_message']) ? $rsp_claim_form_data['initial_message'] : '';

get_header();
?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap');

html {
    background-color: #FF8400;
}


.dial-pad-container {
    width: 85%;
    /* height: 80%; */
    margin: 20px auto;
    padding: 80px 70px;
    border-radius: 20px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    font-family: 'Montserrat', sans-serif;
    background-color: #fff;
    box-sizing: border-box;
}

.referral-input-section {
    background-color: #FF8400 !important;
    padding: 0 24px;
    text-align: center;
    margin-top: 18vw;
}

.page-header {
    background-color: #FF8400 !important;
}


.dial-pad-display-area {
    margin-bottom: 36px;
}

#phoneNumberDisplay {
    width: 100%;
    padding: 10px;
    font-size: 62px;
    font-weight: bold;
    text-align: center;
    box-sizing: border-box;
    border-width: 0 0 8px 0;
    border-style: dashed;
    border-color: #66666633;
    color: #898989;
    margin-bottom: 34px;
    margin-top: 26px;
}

.dial-pad {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    /* 3 kolom */
    gap: 48px;
}

.dial-button {
    padding: 20px;
    font-size: 68px;
    background-color: #2E2F38;
    border: 1px solid #ddd;
    border-radius: 24px;
    cursor: pointer;
    transition: background-color 0.2s;
    text-align: center;
    color: #FFFFFF;
    font-family: 'Montserrat', sans-serif;
}

.dial-button:hover {
    background-color: #e0e0e0;
}

.dial-button:active {
    background-color: #d0d0d0;
}

.page-header {
    text-align: center;
}

.dial-button-extra {
    font-weight: bold;
}

.dial-pad-actions {
    display: flex;
    justify-content: space-between;
    gap: 10px;
}

.delete-button svg {
    width: 52px;
    height: 52px;
}

.dial-action-button {
    flex-grow: 1;
    padding: 12px 15px;
    font-size: 56px;
    border: none;
    border-radius: 24px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    /* Jarak antara ikon dan teks */
}

.dial-action-button svg {
    /* Ukuran default bisa diatur di tag SVGnya, ini hanya fallback */
}

h1 {
    margin: 0;
    display: none;
}

hr {
    display: none;
}

.call-button {
    background-color: #4CAF50;
    /* Hijau */
    color: white;
}

.call-button:hover {
    background-color: #45a049;
}

.delete-button {
    background-color: #f44336;
    /* Merah */
    color: white;
}

.delete-button:hover {
    background-color: #da190b;
}

.dial-head p.heading {
    margin: 0;
    text-align: center;
    font-size: 52px;
    font-family: 'Montserrat', sans-serif;
    font-weight: bold;
    margin-bottom: 6px;
}

.dial-head p.desc {
    margin: 0;
    text-align: center;
    font-size: 26px;
    font-family: 'Montserrat', sans-serif;
    margin-bottom: 30px;
}

.orange-text {
    color: #FF8400;
    font-weight: bold;
    font-style: italic;
}

.jete-logo {
    width: 28vw;
    margin: 32px 0;
}

.sk {
    margin-top: 72px;
    font-size: 32px;
}

@media screen and (max-width: 600px) {
    .referral-input-section {
        margin-top: 0px !important;
    }

    .jete-logo {
        width: 10vw;
        margin: 32px 0;
    }
}
</style>
<div id="primary" class="content-area rsp-claim-form-page">

    <div class="referral-input-section">
        <img class="jete-logo" src="<?php echo esc_url( RSP_PLUGIN_URL . 'assets/images/logo.png' ); ?>"
            alt="<?php esc_attr_e( 'JETE Indonesia', 'referral-store-promo' ); ?>">
        <div class="dial-pad-container">
            <div class="dial-head">
                <p class="heading">Masukan Kode Kasir</p>
                <p class="desc">Silakan lakukan validasi kode melalui kode kasir di <span class="orange-text">
                        Official Store JETE &
                        DORAN GADGET </span> terdekatmu
                </p>
            </div>
            <div class="dial-pad-display-area">
                <input type="text" id="phoneNumberDisplay" readonly placeholder="Kode Store">
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
            <p class="sk">
                Syarat & Ketentuan Promo
            </p>
        </div>
    </div>
    </main>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const display = document.getElementById('phoneNumberDisplay');
    const submitDialButton = document.querySelector('.dial-action-button.call-button'); // Tombol centang (OK)
    const headingElement = document.querySelector('.dial-head p.heading');
    const descElement = document.querySelector('.dial-head p.desc');
    const ajaxResultDiv = document.getElementById('rspClaimAjaxResult');

    if (!display || !submitDialButton || !headingElement || !descElement) {
        console.error('Satu atau lebih elemen UI dial pad tidak ditemukan. Script tidak dijalankan.');
        return;
    }

    let currentInputStep = 'kodeStore';
    let collectedKodeStore = '';

    // Ambil kode_promo dari PHP. Anda HARUS menyesuaikan ini.
    // Contoh: menggunakan slug promo sebagai kode promo.
    // Jika kode promo ada di custom field, gunakan get_post_meta di PHP dan teruskan ke JS.
    const jsKodePromo = <?php
        global $rsp_claim_form_data;
        $promo_object = isset($rsp_claim_form_data['promo_obj']) ? $rsp_claim_form_data['promo_obj'] : null;
        $kode_promo_value = '';
        if ($promo_object && property_exists($promo_object, 'post_name')) {
            // Misalnya, kode promo adalah slug dari post promo
            $kode_promo_value = $promo_object->post_name;
        }
        // Jika Anda punya cara lain untuk mendapatkan kode promo (misal, dari custom field):
        // if ($promo_object) {
        // $kode_promo_value = get_post_meta($promo_object->ID, 'your_meta_key_for_promo_code', true);
        // }
        echo json_encode($kode_promo_value);
    ?>;

    if (!jsKodePromo && currentInputStep === 'kodeStore') { // Hanya penting jika kita butuh ini di step awal
        // console.warn('Kode Promo tidak ditemukan. Pastikan $rsp_claim_form_data["promo_obj"] memiliki kode promo yang valid.');
        // Jika kode promo wajib ada dari awal, Anda bisa tampilkan error di sini atau mencegah form.
        // Untuk skenario ini, kita memerlukannya saat submit akhir.
    }


    function updateDialPadUI(step) {
        display.value = '';
        if (step === 'kodeStore') {
            headingElement.textContent = 'Masukan Kode Kasir';
            descElement.innerHTML =
                'Silakan lakukan validasi kode melalui kode kasir di <span class="orange-text">Official Store JETE & DORAN GADGET</span> terdekatmu';
            display.placeholder = 'Kode Store';
        } else if (step === 'whatsapp') {
            headingElement.textContent = 'Masukan Nomor WA';
            descElement.textContent =
                'Silakan masukan nomor Telf yang terhubung ke Whatsapp';
            display.placeholder = 'Nomor WhatsApp';
        }
    }

    updateDialPadUI(currentInputStep);

    const maxDigits = 15;
    const dialButtons = document.querySelectorAll('.dial-pad .dial-button');
    const deleteDialPadButton = document.getElementById('deleteButton');

    dialButtons.forEach(button => {
        button.addEventListener('click', function() {
            const value = this.dataset.value;
            if (display.value.length < maxDigits) {
                display.value += value;
            }
        });
    });

    if (deleteDialPadButton) {
        deleteDialPadButton.addEventListener('click', function() {
            display.value = display.value.slice(0, -1);
        });
    }

    if (submitDialButton) {
        submitDialButton.addEventListener('click', function() {
            const currentDisplayValue = display.value;

            if (ajaxResultDiv) {
                ajaxResultDiv.style.display = 'none';
                ajaxResultDiv.className = 'rsp-claim-ajax-result';
                ajaxResultDiv.textContent = '';
            }

            if (currentInputStep === 'kodeStore') {
                if (currentDisplayValue.length === 0) {
                    if (ajaxResultDiv) {
                        ajaxResultDiv.textContent = (typeof rsp_script_vars !== 'undefined' &&
                                rsp_script_vars.error_kode_store_empty) ? rsp_script_vars
                            .error_kode_store_empty : 'Kode Store tidak boleh kosong.';
                        ajaxResultDiv.classList.add('rsp-error');
                        ajaxResultDiv.style.display = 'block';
                    } else {
                        alert('Kode Store tidak boleh kosong.');
                    }
                    return;
                }
                collectedKodeStore = currentDisplayValue;
                currentInputStep = 'whatsapp';
                updateDialPadUI(currentInputStep);

            } else if (currentInputStep === 'whatsapp') {
                const noWhatsapp = currentDisplayValue;
                if (noWhatsapp.length === 0) {
                    if (ajaxResultDiv) {
                        ajaxResultDiv.textContent = 'Nomor WhatsApp tidak boleh kosong.';
                        ajaxResultDiv.classList.add('rsp-error');
                        ajaxResultDiv.style.display = 'block';
                    } else {
                        alert('Nomor WhatsApp tidak boleh kosong.');
                    }
                    return;
                }
                if (!/^\d{8,15}$/.test(noWhatsapp)) {
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

                // ----- MULAI PROSES SUBMIT KE WEBHOOK -----
                const webhookUrl =
                    'https://services.leadconnectorhq.com/hooks/EBB7zornJZkBodHpGN3B/webhook-trigger/fd58e12a-3741-4b67-a6b5-81ae48981be2';
                const dataToSend = {
                    "whatsapp": noWhatsapp,
                    "kode_store": collectedKodeStore,
                    "promo": jsKodePromo // Pastikan jsKodePromo berisi nilai kode promo yang benar
                };

                this.disabled = true;
                const originalButtonText = this.innerHTML;
                if (typeof rsp_script_vars !== 'undefined' && rsp_script_vars.submitting_text) {
                    this.innerHTML = rsp_script_vars.submitting_text;
                } else {
                    this.innerHTML = '...'; // Ubah teks jika perlu
                }

                // Thankyou Page
                const thankYouPageUrl = '/thank-you/';

                fetch(webhookUrl, {
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
                    .then(data => { // data di sini bisa berupa string (dari response.text()) atau objek (dari response.json())
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
                            ajaxResultDiv.classList.add(isSuccess ? 'rsp-success' : 'rsp-error');
                            ajaxResultDiv.style.display = 'block';
                        } else {
                            alert(successMessage);
                        }

                        if (isSuccess) {
                            // Reset ke tahap awal atau sembunyikan form
                            currentInputStep = 'kodeStore';
                            collectedKodeStore = '';
                            updateDialPadUI(currentInputStep);
                            // display.value = ''; // sudah dihandle di updateDialPadUI
                            // document.querySelector('.referral-input-section .dial-pad-container').style.display = 'none';
                            // document.getElementById('primary').innerHTML = `<div class="rsp-final-message rsp-success">${successMessage} <p>Terima kasih!</p></div>`;

                            // Alihkan ke halaman terima kasih setelah jeda singkat
                            setTimeout(function() {
                                if (thankYouPageUrl) {
                                    window.location.href = thankYouPageUrl;
                                } else {
                                    console.error(
                                        "URL Halaman Terima Kasih tidak dikonfigurasi.");
                                    // Jika tidak ada URL, mungkin reset form atau tampilkan pesan tetap
                                    if (ajaxResultDiv) ajaxResultDiv.innerHTML =
                                        "Pengiriman berhasil, tetapi URL redirect tidak ada.";
                                }
                            }, 500); // Jeda 1.5 detik sebelum redirect
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
                // ----- SELESAI PROSES SUBMIT KE WEBHOOK -----
            }
        });
    }

    if (typeof rsp_script_vars === 'undefined') {
        console.warn('rsp_script_vars is not defined. Localized messages might be missing.');
    }
});
</script>
<?php
?>