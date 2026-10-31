# Micro_Service

## 1. Deskripsi Proyek

Micro_Service adalah proyek penerapan arsitektur
microservices pada sistem e-commerce sederhana.
Sistem dibangun dengan memisahkan fungsi utama
aplikasi ke dalam beberapa service yang memiliki
tanggung jawab masing-masing.

Proyek ini terdiri dari empat service:

1. **Product Service**: Mengelola katalog produk,
   termasuk informasi produk, harga, dan stok.

2. **Cart Service**: Mengelola keranjang belanja
   pengguna, termasuk penambahan, perubahan, dan
   penghapusan item keranjang.

3. **Review Service**: Mengelola ulasan produk
   melalui operasi CRUD (Create, Read, Update,
   Delete).

4. **Order Service**: Mengelola proses pembuatan
   pesanan (checkout), pengambilan data keranjang
   dan produk dari service lain, penyimpanan detail
   pesanan, serta perubahan status pembayaran.

Setiap service berjalan secara terpisah dan
menggunakan database sesuai konfigurasi masing-masing.
Komunikasi antarlayanan dilakukan melalui REST API.

Fokus utama proyek ini adalah implementasi integrasi
antarservice, pengelolaan transaksi checkout,
validasi data, penanganan kegagalan komunikasi,
dan konsistensi data pesanan.

## 2. Arsitektur Sistem

Sistem menggunakan arsitektur microservices, yaitu setiap layanan memiliki tanggung jawab dan database masing-masing. Seluruh service dijalankan menggunakan Docker Compose dan berkomunikasi melalui REST API dalam jaringan internal Docker.

### 2.1 Komponen Sistem

1. **Product Service**
   - Mengelola data katalog produk, harga, dan stok.
   - Dibangun menggunakan Node.js.
   - Menggunakan MySQL dengan database `productdb`.
   - Port aplikasi: `3000`.

2. **Cart Service**
   - Mengelola keranjang belanja pengguna.
   - Dibangun menggunakan Laravel.
   - Menggunakan PostgreSQL dengan database `cartdb`.
   - Port aplikasi: `3001`.

3. **Review Service**
   - Mengelola ulasan produk.
   - Dibangun menggunakan Flask.
   - Menggunakan MongoDB dengan database `reviewdb`.
   - Port aplikasi: `3002`.

4. **Order Service**
   - Mengelola checkout, pembuatan pesanan, detail pesanan, dan status pembayaran.
   - Dibangun menggunakan Laravel.
   - Menggunakan MySQL dengan database `orderdb`.
   - Port aplikasi: `3003`.

### 2.2 Komunikasi Antarservice

- Order Service mengambil data keranjang dari Cart Service saat proses checkout.
- Order Service mengambil informasi produk dan memeriksa stok melalui Product Service.
- Cart Service menggunakan Product Service untuk mendukung validasi produk pada operasi keranjang.
- Review Service terhubung dengan Product Service untuk mendukung pengelolaan ulasan produk.

Komunikasi antarlayanan menggunakan HTTP melalui alamat service internal Docker.

### 2.3 Database

Setiap service memiliki database terpisah untuk menjaga pemisahan tanggung jawab dan kepemilikan data.

| Service | Database | DBMS |
|---|---|---|
| Product Service | `productdb` | MySQL 8 |
| Cart Service | `cartdb` | PostgreSQL 16 |
| Review Service | `reviewdb` | MongoDB 7 |
| Order Service | `orderdb` | MySQL 8 |

### 2.4 Docker dan Port

| Komponen | Port Host | Port Container |
|---|---:|---:|
| Product Service | 3000 | 3000 |
| Cart Service | 3001 | 3001 |
| Review Service | 3002 | 3002 |
| Order Service | 3003 | 80 |
| Product Database | 3306 | 3306 |
| Cart Database | 5432 | 5432 |
| Review Database | 27017 | 27017 |
| Order Database | 3307 | 3306 |

Docker Compose mengatur proses pembangunan container, konfigurasi environment, koneksi database, health check, dan volume penyimpanan data.

## 3. Prasyarat

Sebelum menjalankan sistem, pastikan perangkat telah memenuhi kebutuhan berikut.

### 3.1 Perangkat Lunak

| Software | Versi Pengujian |
|---|---|
| Docker Engine | 29.7.2 |
| Docker Compose | 5.5.1 |
| Git | Terinstal |
| Sistem Operasi | Windows dengan PowerShell |

### 3.2 Teknologi Pengembangan

Teknologi yang digunakan dalam pengembangan proyek:

| Teknologi | Versi |
|---|---|
| Node.js | 24.21.0 |
| npm | 11.19.0 |
| PHP | 8.3.33 |
| Composer | 2.10.3 |
| Laravel | Sesuai konfigurasi proyek |
| Flask | Sesuai konfigurasi proyek |
| MySQL | 8 |
| PostgreSQL | 16 |
| MongoDB | 7 |

### 3.3 Pemeriksaan Instalasi

Jalankan perintah berikut untuk memastikan Docker dan Git tersedia:

```bash
docker --version
docker compose version
git --version
```

Untuk memeriksa lingkungan pengembangan Node.js dan PHP secara lokal:

```bash
node --version
npm --version
php --version
composer --version
```

Node.js, PHP, dan Composer hanya diperlukan secara lokal apabila ingin melakukan pengembangan atau menjalankan service di luar container Docker.

## 4. Cara Menjalankan Sistem

### 4.1 Clone Repository

Clone repository dari GitHub:

```bash
git clone https://github.com/NebulaEst/Micro-Service.git
```

Masuk ke direktori proyek:

```bash
cd Micro-Service
```

### 4.2 Konfigurasi Environment

Pastikan setiap service memiliki konfigurasi environment yang diperlukan.

Periksa file `.env.example` pada service yang menyediakannya. Buat file `.env` berdasarkan konfigurasi tersebut apabila diperlukan.

Jangan memasukkan password, API key, atau kredensial asli ke repository publik.

### 4.3 Build dan Menjalankan Container

Jalankan seluruh service menggunakan perintah:

```bash
docker compose up --build -d
```

Perintah tersebut akan membangun image dan menjalankan container di background.

### 4.4 Memeriksa Status Container

Periksa status seluruh container:

```bash
docker compose ps
```

Pastikan container service dan database berjalan dengan status yang sesuai.

### 4.5 Memeriksa Log

Untuk melihat log seluruh service:

```bash
docker compose logs
```

Untuk melihat log service tertentu:

```bash
docker compose logs -f order-service
```

### 4.6 Menghentikan Sistem

Untuk menghentikan seluruh container:

```bash
docker compose down
```

Untuk menghentikan container sekaligus menghapus volume database:

```bash
docker compose down -v
```

**Catatan:** Perintah terakhir menghapus data database yang tersimpan pada volume Docker. Gunakan dengan hati-hati.

## 5. Environment Variables

Setiap service menggunakan environment variables untuk mengatur konfigurasi aplikasi, koneksi database, dan komunikasi antarservice.

File `.env.example` disediakan sebagai referensi konfigurasi. Buat file `.env` berdasarkan file tersebut apabila diperlukan.

### 5.1 Cart Service

| Variabel | Fungsi | Contoh |
|---|---|---|
| APP_NAME | Nama aplikasi | Cart Service |
| APP_ENV | Lingkungan aplikasi | local |
| APP_KEY | Kunci enkripsi Laravel | Generated key |
| APP_DEBUG | Mode debug | false |
| APP_URL | URL aplikasi | http://localhost:3001 |
| DB_CONNECTION | Driver database | pgsql |
| DB_HOST | Host database | cart-db |
| DB_PORT | Port database | 5432 |
| DB_DATABASE | Nama database | cartdb |
| DB_USERNAME | Username database | appuser |
| DB_PASSWORD | Password database | placeholder |

### 5.2 Product Service

| Variabel | Fungsi | Contoh |
|---|---|---|
| PORT | Port aplikasi | 3000 |
| DB_HOST | Host database | product-db |
| DB_USER | Username database | placeholder |
| DB_PASSWORD | Password database | placeholder |
| DB_NAME | Nama database | productdb |

### 5.3 Review Service

| Variabel | Fungsi | Contoh |
|---|---|---|
| PORT | Port aplikasi | 3002 |
| HOST | Host aplikasi | 0.0.0.0 |
| FLASK_DEBUG | Mode debug Flask | false |
| MONGO_URI | URI koneksi MongoDB | mongodb://review-db:27017 |
| MONGO_DB_NAME | Nama database MongoDB | reviewdb |
| PRODUCT_SERVICE_URL | URL Product Service | http://product-service:3000 |

### 5.4 Order Service

| Variabel | Fungsi | Contoh |
|---|---|---|
| APP_NAME | Nama aplikasi | Order Service |
| APP_ENV | Lingkungan aplikasi | local |
| APP_KEY | Kunci enkripsi Laravel | Generated key |
| APP_DEBUG | Mode debug | false |
| APP_URL | URL aplikasi | http://localhost:3003 |
| DB_CONNECTION | Driver database | mysql |
| DB_HOST | Host database | order-db |
| DB_PORT | Port database internal | 3306 |
| DB_DATABASE | Nama database | orderdb |
| DB_USERNAME | Username database | placeholder |
| DB_PASSWORD | Password database | placeholder |

### 5.5 Catatan Konfigurasi

- Nilai pada tabel merupakan contoh konfigurasi, bukan kredensial produksi.
- `APP_KEY` harus dibuat menggunakan perintah `php artisan key:generate`.
- `DB_HOST` menggunakan nama service database pada jaringan internal Docker.
- `PRODUCT_SERVICE_URL` digunakan Review Service untuk berkomunikasi dengan Product Service.
- Variabel tambahan seperti session, cache, logging, mail, Redis, dan AWS tersedia pada konfigurasi Laravel, tetapi penggunaannya bergantung pada kebutuhan aplikasi.
- Jangan mengunggah file `.env` yang berisi kredensial asli ke repository publik.

## 6. API Endpoints

Seluruh endpoint dapat diakses melalui `localhost` dengan port masing-masing service.

### 6.1 Product Service

Base URL: `http://localhost:3000`

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/products` | Mengambil seluruh produk |
| GET | `/products/{id}` | Mengambil detail produk |
| POST | `/products` | Menambahkan produk |
| PUT | `/products/{id}` | Memperbarui produk |
| DELETE | `/products/{id}` | Menghapus produk |

**Request POST/PUT:**

```json
{
  "name": "Gk Kunten",
  "description": "Contoh produk",
  "price": 168000,
  "stock": 10,
  "image": "BASE64_IMAGE"
}
```

Ketentuan:
- `name` wajib berupa teks dan tidak boleh kosong.
- `price` wajib berupa angka nol atau lebih.
- `stock` berupa bilangan bulat nol atau lebih; jika tidak dikirim, nilainya `0`.
- `description` harus berupa teks jika dikirim.
- `image` wajib berupa Base64 valid dengan ukuran maksimal 2 MB.

Status respons utama: `200`, `201`, `400`, `404`, dan `500`.

### 6.2 Cart Service

Base URL: `http://localhost:3001`

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/carts/{userId}` | Mengambil keranjang pengguna |
| POST | `/carts` | Menambahkan item ke keranjang |
| PUT | `/carts/items/{id}` | Memperbarui jumlah item |
| DELETE | `/carts/items/{id}` | Menghapus item |
| DELETE | `/carts/{userId}` | Mengosongkan keranjang |

**Request POST `/carts`:**

```json
{
  "user_id": 1,
  "product_id": 1,
  "quantity": 2
}
```

Respons penambahan item:
- `201`: item baru berhasil dibuat.
- `200`: item berhasil ditambahkan ke item keranjang yang sudah ada.
- `404`: produk tidak ditemukan.
- `500`: kesalahan server.

**Request PUT `/carts/items/{id}`:**

```json
{
  "quantity": 3
}
```

Respons utama: `200`, `404`, dan `500`. Validasi request ditangani oleh `AddCartItemRequest` dan `UpdateCartItemRequest`.

### 6.3 Review Service

Base URL: `http://localhost:3002`

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/reviews/product/{product_id}` | Mengambil review berdasarkan produk |
| POST | `/reviews` | Membuat review |
| GET | `/reviews/{review_id}` | Mengambil detail review |
| PUT | `/reviews/{review_id}` | Memperbarui review |
| DELETE | `/reviews/{review_id}` | Menghapus review |

**Request POST `/reviews`:**

```json
{
  "product_id": 1,
  "user_id": 1,
  "rating": 5,
  "comment": "Produk bagus"
}
```

Ketentuan:
- `product_id` dan `user_id` wajib berupa bilangan bulat minimal 1.
- `rating` wajib berupa bilangan bulat 1 sampai 5.
- `comment` berupa teks maksimal 1000 karakter. Jika tidak dikirim, nilainya menjadi string kosong.

**Request PUT `/reviews/{review_id}`:**

```json
{
  "rating": 4,
  "comment": "Review diperbarui"
}
```

Minimal salah satu field `rating` atau `comment` harus dikirim.

Status respons utama:
- `200`: berhasil mengambil, memperbarui, atau menghapus review.
- `201`: review berhasil dibuat.
- `404`: review tidak ditemukan.
- `409`: review duplikat.
- `422`: validasi gagal atau produk tidak ditemukan.
- `503`: Product Service tidak tersedia.
- `500`: kesalahan server.

### 6.4 Order Service

Base URL: `http://localhost:3003`

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/health` | Memeriksa status service |
| POST | `/orders` | Membuat pesanan melalui checkout |
| GET | `/orders/{id}` | Mengambil detail pesanan |
| GET | `/orders/user/{userId}` | Mengambil daftar pesanan pengguna |
| PATCH | `/orders/{id}/pay` | Mengubah status pembayaran |

**Request POST `/orders`:**

```json
{
  "user_id": 1
}
```

Checkout mengambil data keranjang dari Cart Service dan informasi produk dari Product Service. Data produk seperti nama dan harga disimpan sebagai snapshot pada detail pesanan.

**Filter daftar pesanan:**

```text
GET /orders/user/1?status=PENDING
```

Status utama yang ditangani:
- `200`: berhasil.
- `201`: pesanan berhasil dibuat.
- `404`: pesanan tidak ditemukan.
- `409`: konflik checkout atau status pembayaran.
- `422`: validasi request gagal.
- `503`: service dependency tidak tersedia.
- `500`: kesalahan server.

## 7. Design Decisions and Limitations

### 7.1 Design Decisions

**1. Pemisahan Service Berdasarkan Tanggung Jawab**

Sistem menggunakan arsitektur microservices dengan empat service yang memiliki tanggung jawab berbeda:

- Product Service menangani katalog dan informasi produk.
- Cart Service menangani keranjang belanja pengguna.
- Review Service menangani ulasan produk.
- Order Service menangani checkout dan pengelolaan pesanan.

Pemisahan ini memungkinkan setiap service dikembangkan dan dijalankan secara independen.

**2. Database Terpisah**

Setiap service menggunakan database sesuai kebutuhan teknologinya:

- Product Service: MySQL.
- Cart Service: PostgreSQL.
- Review Service: MongoDB.
- Order Service: MySQL.

Pemisahan database membantu menjaga kepemilikan data dan tanggung jawab masing-masing service.

**3. Komunikasi Menggunakan REST API**

Komunikasi antarservice dilakukan melalui HTTP REST API. Order Service mengambil informasi keranjang dari Cart Service dan informasi produk dari Product Service ketika proses checkout berlangsung.

**4. Snapshot Harga Produk**

Order Service menyimpan nama produk dan harga pada saat checkout ke tabel `order_items`. Dengan demikian, perubahan harga pada Product Service tidak mengubah nilai historis pesanan yang sudah dibuat.

**5. Transaksi Database pada Checkout**

Pembuatan order dan order items dilakukan menggunakan transaksi database agar penyimpanan data pesanan tetap konsisten apabila terjadi kegagalan selama proses penyimpanan.

**6. Validasi dan Penanganan Error**

Setiap service menerapkan validasi request dan penanganan error sesuai tanggung jawabnya. Order Service juga menangani kegagalan komunikasi dengan service dependency dan membedakan kesalahan validasi, konflik, serta kegagalan upstream.

### 7.2 Limitations

**1. Belum Menggunakan Distributed Transaction**

Transaksi database hanya mengatur perubahan pada database Order Service. Komunikasi dengan Cart Service dan Product Service tidak termasuk dalam satu transaksi database terdistribusi.

**2. Keterbatasan Konsistensi Antarservice**

Proses checkout melibatkan beberapa service. Oleh karena itu, kegagalan komunikasi atau perubahan data pada service lain dapat memengaruhi hasil proses checkout.

**3. Pengelolaan Stok**

Order Service memeriksa ketersediaan stok melalui Product Service. Implementasi ini belum dapat dianggap sebagai mekanisme reservasi stok atomik lintas service.

**4. Pengosongan Keranjang**

Setelah pesanan berhasil disimpan, Order Service meminta Cart Service mengosongkan keranjang. Kegagalan pada proses tersebut dicatat sebagai warning dan tidak membatalkan pesanan yang sudah berhasil dibuat.

**5. Batasan Ruang Lingkup**

Proyek ini berfokus pada implementasi komunikasi antarservice, proses checkout, penyimpanan pesanan, validasi, dan penanganan error. Implementasi ini belum ditujukan sebagai sistem e-commerce production-ready.


## 8. Demo Video

Video demonstrasi proyek Micro_Service mencakup:

1. Menjalankan seluruh service menggunakan Docker Compose.
2. Memeriksa health endpoint setiap service.
3. Melakukan proses checkout pesanan.
4. Menguji validasi request dan penanganan error.
5. Menguji integrasi Order Service dengan Cart Service dan Product Service.
6. Menampilkan snapshot harga produk pada pesanan.
7. Memeriksa data pesanan dan status pembayaran.

**Demo Video:** `[MASUKKAN LINK VIDEO]`

**GitHub Repository:** https://github.com/NebulaEst/Micro-Service

**Latest Commit:** `[MASUKKAN SHA COMMIT TERBARU]`
## 9. Testing

Pengujian dilakukan untuk memastikan setiap service berjalan dan integrasi antarservice berfungsi sesuai kebutuhan.

| No. | Skenario Pengujian | Hasil yang Diharapkan |
|---|---|---|
| 1 | GET `/health` | Service memberikan status OK |
| 2 | POST `/orders` dengan data valid | Order berhasil dibuat (201) |
| 3 | POST `/orders` dengan body kosong | Validasi gagal (422) |
| 4 | Checkout dengan keranjang kosong | Konflik checkout (409) |
| 5 | Checkout dengan produk tidak ditemukan | Konflik checkout (409) |
| 6 | Checkout dengan stok tidak mencukupi | Konflik checkout (409) |
| 7 | Product Service tidak tersedia | Upstream error (503) |
| 8 | Cart Service tidak tersedia | Upstream error (503) |
| 9 | GET `/orders/{id}` dengan ID valid | Detail pesanan ditampilkan (200) |
| 10 | GET `/orders/{id}` dengan ID tidak tersedia | Pesanan tidak ditemukan (404) |
| 11 | GET `/orders/user/{userId}` | Daftar pesanan pengguna ditampilkan (200) |
| 12 | Filter status pesanan tidak valid | Validasi gagal (422) |
| 13 | PATCH `/orders/{id}/pay` | Status pembayaran berubah sesuai aturan |
| 14 | Perubahan harga produk setelah checkout | Harga pada pesanan lama tetap sama |

**Catatan:** Tabel ini merupakan daftar skenario pengujian yang ditargetkan. Status aktual setiap skenario perlu dicantumkan setelah pengujian dan verifikasi dilakukan.