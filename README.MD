# Micro_Service

## 1. Deskripsi Proyek

TODO: Jelaskan tujuan sistem microservices dan fungsi
masing-masing service berdasarkan implementasi aktual.

## 2. Arsitektur Sistem

TODO: Jelaskan hubungan product-service, cart-service,
review-service, order-service, serta database masing-masing.

## 3. Prasyarat

TODO: Cantumkan kebutuhan instalasi dan versi yang sudah
dipastikan sesuai dengan konfigurasi akhir proyek.

## 4. Cara Menjalankan Sistem

TODO: Lengkapi setelah docker compose up --build berhasil
diuji dari hasil clone repository yang baru.

## 5. Environment Variables

TODO: Cantumkan nama variabel, fungsi, dan nilai contoh
yang aman. Jangan masukkan rahasia sungguhan.

## 6. Daftar Endpoint API

TODO: Cantumkan endpoint, metode HTTP, contoh request,
dan response berdasarkan implementasi dan hasil pengujian.

## 7. Keputusan Desain dan Keterbatasan

TODO: Dokumentasikan keputusan desain dan keterbatasan
yang benar-benar ditemukan selama pengerjaan.

## 8. Video Demo

TODO: Tambahkan tautan video yang dapat diakses publik
serta penanda waktu setiap bagian demo.

## 3. Struktur Repository

- `product-service/`: layanan katalog produk.
- `cart-service/`: layanan keranjang belanja.
- `review-service/`: layanan ulasan produk.
- `order-service/`: layanan pembuatan dan pengelolaan pesanan.
- `docker-compose.yml`: konfigurasi untuk menjalankan sistem.

## Skenario Pengujian

| Skenario | Endpoint | Hasil yang Diharapkan |
|---|---|---|
| Health check berhasil | GET /health | 200 |
| Checkout berhasil | POST /orders | 201 |
| Body tidak valid | POST /orders | 422 |
| Keranjang kosong | POST /orders | 409 |
| Service eksternal bermasalah | POST /orders | 503 |

TODO: Lengkapi seluruh skenario wajib berdasarkan hasil
pengujian aktual, termasuk skenario endpoint lainnya.