# Backend API - Manajemen Paket Test


---
## Fitur Utama

### Manajemen Tes
- CRUD paket tes (nama tes, posisi target, tipe akses)
- Penambahan section: tipe tes, durasi, urutan
- Duplikasi paket tes termasuk section dan isi pertanyaan di dalamnya. sehingga admin dapat mengcopy satu paket tes lengkap (beserta section & soal) sebagai dasar untuk pembuatan template posisi tertentu 

### Manajemen Pertanyaan Tes
- CRUD soal per section (berdasarkan jenis: DISC, CAAS, atau teliti)
- Soal dapat ditambahkan berdasarkan ID & jenis soal

---

## Struktur Database

### `tests`
- `id`
- `name`
- `target_position`
- `icon_path`
- `started_date`
- `access_type` → `Invitation Only | Public`
- `timestamps`

### `test_sections`
- `id`
- `test_id`
- `section_type` → `DISC | CAAS | teliti`
- `duration_minutes`
- `question_count`
- `sequence`
- `timestamps`

### `test_questions`
- `id`
- `test_id`
- `section_id` 
- `question_id`
- `question_type` → `caas | disc | teliti`
- `timestamps`

---


##  API Endpoint

### Manajemen Tes
- `GET /api/test-package` - Ambil semua tes
- `POST /api/test-package` - Buat tes baru beserta sections
- `GET /api/test-package/{id}` - Lihat detail tes + sections
- `PUT /api/test-package/{id}` - Update data tes & sections
- `DELETE /api/test-package/{id}` - Hapus tes
- `POST /api/test-package/{id}/duplicate` - Melakukan duplikat tes beserta seluruh soal dan sectionnya

### Manajemen Pertanyaan Tes
- `GET /api/manage-questions` - Lihat semua soal dalam test (dengan relasi test & section)
- `POST /api/manage-questions` - Tambah soal ke dalam test
- `GET /api/manage-questions/{id}` - Lihat 1 soal test
- `PUT /api/manage-questions/{id}` - Update soal test
- `DELETE /api/manage-questions/{id}` - Hapus soal test

---

### Contoh Payload API

## Manajemen Tes
Create Test
```json
{
    "name": "Tes Manager",
    "target_position": "Manager",
    "started_date" : "2025-07-01",
    "access_type": "Public",
    "sections": [
        {
            "section_type": "teliti",
            "duration_minutes": 10,
            "question_count": 20,
            "sequence": 1
        },
        {
            "section_type": "CAAS",
            "duration_minutes": 15,
            "question_count": 25,
            "sequence": 2
        }
    ],
    "token": "..."
}
```
Duplicate tes

```json
"token": "..."
```
### Manajemen Soal
Menambahkan pertanyaan pada Paket test
```json
{
    "questions": [
        {
            "test_id": 6,
            "question_id": 1,
            "question_type": "teliti",
            "section_id": 13
        },
        {
            "test_id": 6,
            "question_id": 1,
            "question_type": "caas",
            "section_id": 14
        }
    ],
   "token": "..."
}
``` 

Update pertanyaa
```json
{
    "test_id": 6,
    "question_id": 1,
    "question_type": "teliti",
    "section_id": 13,
    "token": "..."
}
```
