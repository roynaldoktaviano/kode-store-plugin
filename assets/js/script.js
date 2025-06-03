// Syarat dan Ketentuan Pop Up


document.addEventListener('DOMContentLoaded', function() {
  const display = document.getElementById('phoneNumberDisplay');
  const dialButtons = document.querySelectorAll('.dial-button');
  const callButton = document.getElementById('callButton');
  const deleteButton = document.getElementById('deleteButton');

  let currentNumber = '';


  dialButtons.forEach(button => {
    button.addEventListener('click', function() {
      const value = this.dataset.value;
      if (currentNumber.length < 15) { // Batasi panjang nomor
        currentNumber += value;
        display.value = currentNumber;
      }
    });
  });

  if (deleteButton) {
    deleteButton.addEventListener('click', function() {
      currentNumber = currentNumber.slice(0, -1);
      display.value = currentNumber;
    });
  }
});

// Variabel global untuk tanggal hitung mundur
var countdownDate = new Date(Date.parse(new Date()) + 14 * 24 * 60 * 60 * 1000);

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
	topFlip.className = "top-flip"; // Menggunakan className untuk kompatibilitas lebih luas daripada classList
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

		if (t.diff <= 0) {
			clearInterval(timeinterval);
		}
	}

	updateClock(); // Panggil sekali di awal agar tidak ada jeda
	timeinterval = setInterval(updateClock, 1000);
}

// Mulai jam hitung mundur
initializeClock(countdownDate);

document.addEventListener('DOMContentLoaded', function() {
    // Cek apakah elemen-elemen yang dibutuhkan ada di halaman ini
    // Ini penting agar script tidak error di halaman lain yang tidak memiliki elemen ini.
    const display = document.getElementById('rspKodeStoreDisplay');
    const submitButton = document.getElementById('rspSubmitKodeStore');

    // Jika elemen utama tidak ditemukan, kemungkinan kita tidak di halaman klaim, jadi hentikan script.
    if (!display || !submitButton) {
        // console.log('Elemen dial pad atau tombol submit tidak ditemukan. Script klaim tidak dijalankan.');
        return;
    }

    // --- Logika untuk Dial Pad (Vanilla JS) ---
    const maxDigits = 10; // Batas maksimal digit, sesuaikan jika perlu
    const dialButtons = document.querySelectorAll('.dial-pad .dial-button');
    const deleteButton = document.querySelector('.dial-pad .rsp-delete-button'); // Tombol backspace di dalam dialpad

    dialButtons.forEach(button => {
        button.addEventListener('click', function() {
            const value = this.dataset.value; // Mengambil dari data-value
            if (display.value.length < maxDigits) {
                display.value += value;
            }
        });
    });

    if (deleteButton) {
        deleteButton.addEventListener('click', function() {
            display.value = display.value.slice(0, -1);
        });
    }

    // --- Logika untuk Tombol Submit Kode Store (AJAX dengan Fetch API) ---
    const ajaxResultDiv = document.getElementById('rspClaimAjaxResult');

    submitButton.addEventListener('click', function() {
        const kodeStore = display.value;
        const promoId = this.dataset.promoId;
        // const promoSlug = this.dataset.promoSlug; // Jika Anda mengirim slug juga

        // Reset dan sembunyikan pesan hasil AJAX sebelumnya
        if(ajaxResultDiv){
            ajaxResultDiv.style.display = 'none';
            ajaxResultDiv.className = 'rsp-claim-ajax-result'; // Reset class
            ajaxResultDiv.textContent = '';
        }


        if (kodeStore.length === 0) {
            if(ajaxResultDiv && typeof rsp_script_vars !== 'undefined' && rsp_script_vars.error_kode_store_empty) {
                ajaxResultDiv.textContent = rsp_script_vars.error_kode_store_empty;
                ajaxResultDiv.classList.add('rsp-error');
                ajaxResultDiv.style.display = 'block';
            } else {
                alert('Kode Store tidak boleh kosong.'); // Fallback jika rsp_script_vars tidak ada
            }
            return;
        }

        // Nonaktifkan tombol dan ubah teks selama proses
        this.disabled = true;
        const originalButtonText = this.innerHTML; // Simpan teks/HTML asli tombol
        if (typeof rsp_script_vars !== 'undefined' && rsp_script_vars.submitting_text) {
            this.innerHTML = rsp_script_vars.submitting_text; // Teks "Memproses..."
        } else {
            this.innerHTML = 'Memproses...';
        }


        // Siapkan data untuk dikirim. Menggunakan URLSearchParams untuk format x-www-form-urlencoded
        const formData = new URLSearchParams();
        formData.append('action', 'rsp_process_claim_with_store_code');
        formData.append('security', (typeof rsp_script_vars !== 'undefined' ? rsp_script_vars.nonce : '')); // Ambil nonce jika ada
        formData.append('kode_store', kodeStore);
        formData.append('promo_id', promoId);
        // formData.append('promo_slug', promoSlug); // Jika perlu

        fetch((typeof rsp_script_vars !== 'undefined' ? rsp_script_vars.ajax_url : '/wp-admin/admin-ajax.php'), { // Ambil ajax_url jika ada
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData.toString() // Kirim data sebagai string query
        })
        .then(response => {
            if (!response.ok) {
                // Jika response HTTP tidak OK (misal 404, 500), lempar error
                return response.text().then(text => { throw new Error('Server error: ' + response.status + " " + text) });
            }
            return response.json(); // Parse response sebagai JSON
        })
        .then(data => { // Data adalah objek JSON yang sudah diparsing
            if (ajaxResultDiv) {
                ajaxResultDiv.innerHTML = data.data.message || (data.success ? 'Sukses!' : (typeof rsp_script_vars !== 'undefined' && rsp_script_vars.error_generic ? rsp_script_vars.error_generic : 'Terjadi kesalahan.'));
                if (data.success) {
                    ajaxResultDiv.classList.add('rsp-success');
                    // display.value = ''; // Opsional: kosongkan display setelah sukses
                    // document.querySelector('.referral-input-section .dial-pad-container').style.display = 'none'; // Opsional
                } else {
                    ajaxResultDiv.classList.add('rsp-error');
                }
                ajaxResultDiv.style.display = 'block';
            } else if (data.data.message) {
                alert(data.data.message); // Fallback alert
            }

        })
        .catch(error => {
            console.error('Fetch Error:', error);
            if (ajaxResultDiv) {
                ajaxResultDiv.textContent = (typeof rsp_script_vars !== 'undefined' && rsp_script_vars.error_ajax ? rsp_script_vars.error_ajax : 'Kesalahan koneksi. Silakan coba lagi.');
                ajaxResultDiv.classList.add('rsp-error');
                ajaxResultDiv.style.display = 'block';
            } else {
                alert('Terjadi kesalahan koneksi.'); // Fallback alert
            }
        })
        .finally(() => {
            // Aktifkan kembali tombol dan kembalikan teks aslinya
            this.disabled = false;
            this.innerHTML = originalButtonText;
        });
    });

    // Pastikan rsp_script_vars ada (dilokalisasi dari PHP)
    // Jika tidak, beberapa pesan mungkin tidak muncul atau menggunakan fallback
    if (typeof rsp_script_vars === 'undefined') {
        console.warn('rsp_script_vars is not defined. AJAX URL, nonce, and localized messages might be missing.');
    }
});

