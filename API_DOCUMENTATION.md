# PT BGA Fossnir API Documentation

Dokumentasi lengkap untuk API PT BGA Fossnir yang dibangun menggunakan Hyperf Framework.

## Base URL

```
http://localhost:9501
```

## Import Postman Collection

File Postman Collection tersedia di: `postman_collection.json`

Untuk menggunakan:
1. Buka Postman
2. Klik **Import**
3. Pilih file `postman_collection.json`
4. Collection akan tersedia dengan semua endpoint

## Environment Variables

Set variable `base_url` di Postman environment Anda:
- **Variable**: `base_url`
- **Value**: `http://localhost:9501` (atau URL server Anda)

---

## API Endpoints

### 1. News API

#### GET /news
Mendapatkan berita terbaru dengan filter opsional berdasarkan mill_id.

**Query Parameters:**
- `mill_id` (optional): Filter berdasarkan mill ID

**Example:**
```
GET /news?mill_id=1
```

---

### 2. Fossnir API

#### GET /fossnir/mill
Mendapatkan daftar mill dengan paginasi (10 per halaman).

**Example:**
```
GET /fossnir/mill
```

#### GET /fossnir/mill/{id}
Mendapatkan detail mill berdasarkan ID.

**Path Parameters:**
- `id` (required): Mill ID

**Example:**
```
GET /fossnir/mill/1
```

#### PUT /fossnir/mill/{id}
Update informasi mill.

**Path Parameters:**
- `id` (required): Mill ID

**Request Body:**
```json
{
  "mill_name": "Updated Mill Name",
  "order": 1
}
```

#### DELETE /fossnir/mill/{id}
Menghapus mill berdasarkan ID.

**Path Parameters:**
- `id` (required): Mill ID

#### GET /fossnir/report/{id}
Mendapatkan laporan mill dengan filter tanggal dan produk.

**Path Parameters:**
- `id` (required): Mill ID

**Query Parameters:**
- `from` (optional): Tanggal mulai (default: kemarin)
- `to` (optional): Tanggal akhir (default: hari ini)
- `product` (optional): Filter nama produk
- `parameter` (optional): Filter parameter

**Example:**
```
GET /fossnir/report/1?from=2025-01-01&to=2025-01-05&product=ProductA
```

#### GET /fossnir/report/{id}/losses
Mendapatkan data losses berdasarkan parameter.

**Path Parameters:**
- `id` (required): Mill ID

**Query Parameters:**
- `from` (optional): Tanggal mulai
- `to` (optional): Tanggal akhir
- `parameter` (optional): Parameter (default: Oil/WM)

**Example:**
```
GET /fossnir/report/1/losses?from=2025-01-01&to=2025-01-05&parameter=Oil/WM
```

#### GET /fossnir/products/{id}
Mendapatkan daftar nama produk untuk mill tertentu.

**Path Parameters:**
- `id` (required): Mill ID

**Query Parameters:**
- `from` (optional): Tanggal mulai
- `to` (optional): Tanggal akhir

**Example:**
```
GET /fossnir/products/1?from=2025-01-01&to=2025-01-05
```

#### GET /fossnir/mills
Mendapatkan semua mill yang diurutkan berdasarkan field 'order'.

**Example:**
```
GET /fossnir/mills
```

---

### 3. Data API

#### GET /fossnir/stations
Mendapatkan semua stations/groups fossnir.

**Example:**
```
GET /fossnir/stations
```

#### GET /fossnir/data
Mendapatkan data fossnir dengan interval 2 jam dari jam 05:00 hingga 05:00 hari berikutnya.

**Query Parameters:**
- `date` (optional): Tanggal (default: hari ini)
- `group_id` (optional): Group ID (default: 4)
- `parameter` (optional): Parameter - owm, vm, odm, nos (default: owm)
- `mill_id` (optional): Filter mill ID, gunakan 999 untuk semua mill

**Parameters:**
- `owm` = Oil/WM
- `vm` = VM
- `odm` = Oil/DM
- `nos` = NOS

**Example:**
```
GET /fossnir/data?date=2025-01-05&group_id=4&parameter=owm&mill_id=1
```

#### GET /fossnir/daily
Mendapatkan ringkasan data fossnir harian.

**Query Parameters:**
- `date` (optional): Tanggal
- `group_id` (optional): Group ID
- `parameter` (optional): Parameter

**Example:**
```
GET /fossnir/daily?date=2025-01-05&group_id=4&parameter=owm
```

---

### 4. Telegram API

#### GET /telegram/users
Mendapatkan daftar pengguna telegram dengan paginasi (25 per halaman).

**Example:**
```
GET /telegram/users
```

---

### 5. Threshold API

#### GET /thresholds
Mendapatkan thresholds dengan paginasi (200 per halaman).

**Query Parameters:**
- `mill_id` (required): Mill ID
- `parameter` (optional): Filter parameter
- `group_id` (optional): Filter group ID

**Example:**
```
GET /thresholds?mill_id=1
```

#### POST /threshold-update
Membuat atau update nilai threshold.

**Request Body:**
```json
{
  "mill_id": 1,
  "group_id": 4,
  "parameter": "owm",
  "threshold": 10.5
}
```

#### DELETE /threshold-delete/{id}
Menghapus threshold berdasarkan ID.

**Path Parameters:**
- `id` (required): Threshold ID

#### GET /groups
Mendapatkan group products dengan paginasi (200 per halaman).

**Query Parameters:**
- `mill_id` (optional): Filter mill ID
- `group_id` (optional): Filter group ID

**Example:**
```
GET /groups?mill_id=1
```

#### POST /group-update
Membuat atau update group product.

**Request Body:**
```json
{
  "mill_id": 1,
  "group_id": 4,
  "product_name": "Product Name"
}
```

#### DELETE /group-delete/{id}
Menghapus group product berdasarkan ID.

**Path Parameters:**
- `id` (required): Group Product ID

#### GET /products
Mendapatkan daftar nama produk dari tabel FossnirData.

**Query Parameters:**
- `mill_id` (required): Mill ID

**Example:**
```
GET /products?mill_id=1
```

---

### 6. Performance API

#### GET /mill/performance
Mendapatkan skor performa mill berdasarkan hari/bulan/tahun.

**Query Parameters:**
- `mill_id` (required): Mill ID
- `type` (required): Tipe - day, month, atau year
- `date` (optional): Tanggal untuk perhitungan (default: hari ini)

**Example:**
```
GET /mill/performance?mill_id=1&type=month&date=2025-01-05
```

---

### 7. Standard Oil Losses API

#### GET /std/oil/losses
Mendapatkan daftar standard oil losses dengan paginasi.

**Query Parameters:**
- `rowsPerPage` (optional): Jumlah baris per halaman (default: 15)
- `mill_id` (optional): Filter mill ID

**Example:**
```
GET /std/oil/losses?rowsPerPage=15&mill_id=1
```

#### GET /std/oil/losses/{id}
Mendapatkan data oil loss berdasarkan ID.

**Path Parameters:**
- `id` (required): Oil Loss ID

#### POST /std/oil/losses
Membuat atau update standard value oil loss.

**Request Body:**
```json
{
  "mill_id": 1,
  "parameter": "Oil/WM",
  "product_name": "Product Name",
  "std_value": 5.5
}
```

#### DELETE /std/oil/losses/{id}
Menghapus oil loss record berdasarkan ID.

**Path Parameters:**
- `id` (required): Oil Loss ID

#### GET /std/oil/products/{id}
Mendapatkan daftar nama produk untuk mill tertentu dari FossnirProduct.

**Path Parameters:**
- `id` (required): Mill ID

**Example:**
```
GET /std/oil/products/1
```

---

### 8. API Receiver

#### POST /api/oil/loses
Mengirim data oil loses untuk dicatat di tabel samples.

**Request Body:**
```json
{
  "data": [
    {
      "sample_date": "2025-01-05 10:00:00",
      "device_id": 1,
      "product_name": "Product A",
      "parameter": "Oil/WM",
      "result": 5.5
    },
    {
      "sample_date": "2025-01-05 11:00:00",
      "device_id": 1,
      "product_name": "Product B",
      "parameter": "Oil/WM",
      "result": 6.2
    }
  ]
}
```

**Response:**
```json
{
  "error": 0,
  "message": "data successfuly record",
  "count": 2
}
```

#### GET /api/samples
Mendapatkan 50 sample terakhir diurutkan berdasarkan tanggal descending.

**Query Parameters:**
- `device_id` (optional): Filter device ID

**Example:**
```
GET /api/samples?device_id=1
```

**Response:**
```json
{
  "error": 0,
  "data": [...]
}
```

---

## Response Format

Sebagian besar API menggunakan format response standar:

```json
{
  "data": {...},
  "meta": {...}
}
```

Untuk error responses:
```json
{
  "error": 1,
  "message": "Error message"
}
```

---

## Authentication

Saat ini API tidak memiliki authentication mechanism yang terlihat di controller. Pastikan untuk menambahkan authentication jika diperlukan untuk production.

---

## Notes

- Semua tanggal menggunakan format: `Y-m-d` atau `Y-m-d H:i:s`
- Cutoff waktu untuk laporan harian adalah jam 05:00 pagi
- Default interval untuk data adalah 2 jam
- Beberapa endpoint menggunakan pagination dengan default values yang berbeda-beda

---

## Testing dengan Postman

1. Import collection `postman_collection.json`
2. Set environment variable `base_url`
3. Semua endpoint siap untuk di-test
4. Ganti nilai path parameters dan query parameters sesuai kebutuhan

---

## Tech Stack

- **Framework**: Hyperf (PHP)
- **Database**: MySQL
- **ORM**: Hyperf Database
