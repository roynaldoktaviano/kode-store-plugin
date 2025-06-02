document.addEventListener('DOMContentLoaded', function() {
    const display = document.getElementById('phoneNumberDisplay'); // ID input display utama
    const submitDialButton = document.querySelector('.dial-action-button.call-button'); // Tombol submit/centang hijau
    const headingElement = document.querySelector('.dial-head p.heading');
    const descElement = document.querySelector('.dial-head p.desc');
    const ajaxResultDiv = document.getElementById('rspClaimAjaxResult'); // Div untuk menampilkan pesan

    // 1. Pengecekan elemen UI inti (kecuali ajaxResultDiv yang opsional di sini)
    if (!display || !submitDialButton || !headingElement || !descElement) {
        console.error('Satu atau lebih elemen UI dial pad utama tidak ditemukan. Script dial pad tidak dijalankan.');
        // Jika elemen utama tidak ada, tampilkan pesan di ajaxResultDiv jika ada
        if (ajaxResultDiv) {
            ajaxResultDiv.textContent = 'Error: Komponen UI halaman tidak lengkap.';
            ajaxResultDiv.classList.add('rsp-error');
            ajaxResultDiv.style.display = 'block';
        }
        return;
    }

    // 2. Pengecekan rsp_params (data dari PHP)
    if (typeof rsp_params === 'undefined') {
        console.error('Error: rsp_params is not defined. Pastikan wp_localize_script dipanggil dengan benar.');
        if (ajaxResultDiv) {
            ajaxResultDiv.textContent = 'Error konfigurasi: Gagal memuat data promo. Silakan coba lagi nanti atau hubungi administrator.';
            ajaxResultDiv.classList.add('rsp-error');
            ajaxResultDiv.style.display = 'block';
        }
        // Sembunyikan dial pad jika konfigurasi gagal
        const dialPadContainer = document.querySelector('.dial-pad-container');
        if (dialPadContainer) dialPadContainer.style.display = 'none';
        return;
    }

    // 3. Pengecekan apakah promo aktif (berdasarkan data dari rsp_params)
    if (!rsp_params.isPromoActive) {
        console.warn('Promo tidak aktif. Pesan: ' + rsp_params.initialPromoMessage);
        if (ajaxResultDiv) {
            ajaxResultDiv.textContent = rsp_params.initialPromoMessage || 'Promo saat ini tidak tersedia.';
            ajaxResultDiv.classList.add('rsp-error'); // Atau 'rsp-info' jika ingin styling beda
            ajaxResultDiv.style.display = 'block';
        }
        // Sembunyikan dial pad jika promo tidak aktif
        const dialPadContainer = document.querySelector('.dial-pad-container'); // Sesuaikan jika selektor beda
        if (dialPadContainer) {
            dialPadContainer.style.display = 'none';
        }
        return; // Hentikan eksekusi script dial pad
    }

    // Jika semua pengecekan lolos dan promo aktif, lanjutkan dengan logika dial pad
    console.log('Dial pad script initialized. Promo is active.');

    let currentInputStep = 'kodeStore'; // 'kodeStore' atau 'whatsapp'
    let collectedKodeStore = '';

    const jsKodePromo = rsp_params.jsKodePromo;
    const webhookUrl = rsp_params.webhookUrl;
    const thankYouPageUrl = rsp_params.thankYouPageUrl;

    // Fungsi untuk update UI dial pad (judul, deskripsi, placeholder)
    function updateDialPadUI(step) {
        display.value = ''; // Selalu kosongkan display saat step berubah
        if (step === 'kodeStore') {
            headingElement.textContent = 'Masukan Kode Kasir';
            descElement.innerHTML =
                'Silakan lakukan validasi kode melalui kode kasir di <span class="orange-text">Official Store JETE & DORAN GADGET</span> terdekatmu';
            display.placeholder = 'Kode Store';
        } else if (step === 'whatsapp') {
            headingElement.textContent = 'Masukan Nomor WA';
            descElement.innerHTML = // Ganti ke innerHTML jika ada span atau HTML lain
                'Silakan masukan nomor Telf yang terhubung ke Whatsapp';
            display.placeholder = 'Nomor WhatsApp';
        }
    }

    updateDialPadUI(currentInputStep); // Inisialisasi UI untuk step pertama

    const maxDigits = 15; // Batas maksimal digit untuk input
    const dialButtons = document.querySelectorAll('.dial-pad .dial-button[data-value]'); // Tombol angka 0-9
    const deleteDialPadButton = document.getElementById('deleteButton'); // Tombol hapus/backspace

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
            display.value = display.value.slice(0, -1); // Hapus karakter terakhir
        });
    }

    // Logika untuk tombol submit utama (tombol centang hijau)
    if (submitDialButton) {
        submitDialButton.addEventListener('click', function() {
            const currentDisplayValue = display.value;

            // Reset pesan AJAX sebelumnya
            if (ajaxResultDiv) {
                ajaxResultDiv.style.display = 'none';
                ajaxResultDiv.className = 'rsp-claim-ajax-result'; // Reset class
                ajaxResultDiv.textContent = '';
            }

            if (currentInputStep === 'kodeStore') {
                if (currentDisplayValue.length === 0) {
                    if (ajaxResultDiv) {
                        ajaxResultDiv.textContent = rsp_params.error_kode_store_empty || 'Kode Store tidak boleh kosong.';
                        ajaxResultDiv.classList.add('rsp-error');
                        ajaxResultDiv.style.display = 'block';
                    } else {
                        alert(rsp_params.error_kode_store_empty || 'Kode Store tidak boleh kosong.');
                    }
                    return;
                }
                collectedKodeStore = currentDisplayValue;
                currentInputStep = 'whatsapp';
                updateDialPadUI(currentInputStep); // Update UI ke step berikutnya
                // display.value dikosongkan oleh updateDialPadUI

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
                // Validasi format nomor WhatsApp (sederhana: 8-15 digit angka)
                if (!/^\d{8,15}$/.test(noWhatsapp)) {
                    if (ajaxResultDiv) {
                        ajaxResultDiv.textContent = 'Format Nomor WhatsApp tidak valid (8-15 digit angka).';
                        ajaxResultDiv.classList.add('rsp-error');
                        ajaxResultDiv.style.display = 'block';
                    } else {
                        alert('Format Nomor WhatsApp tidak valid (8-15 digit angka).');
                    }
                    return;
                }

                if (!jsKodePromo) {
                    console.error('Kode Promo (jsKodePromo dari rsp_params) tidak ditemukan untuk submit akhir.');
                    if (ajaxResultDiv) {
                        ajaxResultDiv.textContent = 'Error Internal: Kode Promo tidak ditemukan. Tidak bisa mengirim data.';
                        ajaxResultDiv.classList.add('rsp-error');
                        ajaxResultDiv.style.display = 'block';
                    } else {
                        alert('Error Internal: Kode Promo tidak ditemukan. Tidak bisa mengirim data.');
                    }
                    return;
                }

                if (!webhookUrl) {
                    console.error('Webhook URL (dari rsp_params) tidak ditemukan.');
                     if (ajaxResultDiv) {
                        ajaxResultDiv.textContent = 'Error Konfigurasi: URL Webhook tidak tersedia.';
                        ajaxResultDiv.classList.add('rsp-error');
                        ajaxResultDiv.style.display = 'block';
                    }
                    return; // Jangan disable tombol jika URL tidak ada, agar user tidak terjebak
                }

                const dataToSend = {
                    "whatsapp": noWhatsapp,
                    "kode_store": collectedKodeStore,
                    "promo": jsKodePromo // Ini adalah slug promo
                };

                this.disabled = true; // Nonaktifkan tombol submit
                const originalButtonText = this.innerHTML;
                this.innerHTML = rsp_params.submitting_text || 'Memproses...';

                fetch(webhookUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(dataToSend)
                })
                .then(response => {
                    if (response.ok) {
                        return response.text(); // LeadConnector biasanya mengembalikan teks
                    } else {
                        // Coba baca error dari body jika ada, fallback ke statusText
                        return response.text().then(text => {
                            throw new Error(
                                `Webhook Gagal: ${response.status} ${response.statusText} - Pesan Server: ${text || 'Tidak ada detail tambahan.'}`
                            );
                        });
                    }
                })
                .then(data => { // data di sini adalah string dari response.text()
                    let successMessage = "Voucher Berhasil di Klaim!"; // Default pesan sukses
                    let isSuccess = false;

                    // Penanganan respons dari LeadConnector
                    // Biasanya mengembalikan string "success" atau pesan error dalam format teks/JSON.
                    if (typeof data === 'string') {
                        if (data.toLowerCase().includes('success')) {
                            isSuccess = true;
                            // Anda bisa menyesuaikan successMessage jika webhook mengembalikan pesan spesifik
                            // if (data !== "success") { successMessage = data; }
                        } else {
                            // Jika tidak ada kata "success", anggap ada masalah atau pesan error
                            isSuccess = false;
                            successMessage = `Respon Webhook: ${data}`;
                        }
                    } else {
                        // Jika webhook mengembalikan format lain (misal JSON yang tidak terduga)
                        isSuccess = false;
                        successMessage = `Respon Webhook tidak dikenal: ${JSON.stringify(data)}`;
                    }


                    if (ajaxResultDiv) {
                        ajaxResultDiv.innerHTML = successMessage; // Gunakan innerHTML jika pesan ada HTML tags
                        ajaxResultDiv.classList.remove('rsp-error', 'rsp-success');
                        ajaxResultDiv.classList.add(isSuccess ? 'rsp-success' : 'rsp-error');
                        ajaxResultDiv.style.display = 'block';
                    } else {
                        alert(successMessage); // Fallback jika ajaxResultDiv tidak ada
                    }

                    if (isSuccess) {
                        // Reset ke tahap awal atau persiapkan redirect
                        currentInputStep = 'kodeStore';
                        collectedKodeStore = '';
                        // display.value dikosongkan oleh updateDialPadUI
                        // updateDialPadUI(currentInputStep); // Reset UI ke awal jika tidak redirect

                        if (thankYouPageUrl) {
                            setTimeout(function() {
                                window.location.href = thankYouPageUrl;
                            }, 1500); // Jeda 1.5 detik sebelum redirect agar user bisa baca pesan
                        } else {
                            console.warn("URL Halaman Terima Kasih (thankYouPageUrl dari rsp_params) tidak dikonfigurasi.");
                            // Jika tidak ada redirect, mungkin aktifkan tombol lagi dan reset UI
                            this.disabled = false;
                            this.innerHTML = originalButtonText;
                            updateDialPadUI(currentInputStep);
                        }
                    } else {
                        // Jika tidak sukses, aktifkan kembali tombol agar user bisa coba lagi
                        this.disabled = false;
                        this.innerHTML = originalButtonText;
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
                    // Aktifkan kembali tombol jika ada error
                    this.disabled = false;
                    this.innerHTML = originalButtonText;
                })
                .finally(() => {
                    // Logika di .finally dijalankan setelah .then atau .catch
                    // Tombol sudah dihandle di .then (jika sukses dan ada redirect) dan .catch
                    // Jika tidak ada redirect di .then.isSuccess, tombol juga sudah dihandle.
                    // Jadi, blok .finally ini mungkin tidak perlu mengubah state tombol lagi,
                    // kecuali jika ada kasus spesifik lain.
                });
            }
        });
    }
});