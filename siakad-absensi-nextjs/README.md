# SIAKAD V2 — Sistem Absensi Dosen (Next.js untuk Vercel)

Ini adalah hasil migrasi penuh dari project PHP native (`ABSENSI-DOSEN`) ke **Next.js 14 (App Router)**
supaya bisa di-deploy ke **Vercel**. Semua fitur asli dipertahankan 1:1: login, dashboard, input
absensi, rekap kehadiran, dan CRUD data mahasiswa. Query SQL dibuat sengaja identik dengan versi PHP
supaya mudah ditelusuri/dijelaskan.

## Kenapa arsitekturnya berubah?

Vercel tidak menjalankan PHP dan tidak menyediakan MySQL sendiri (server-nya *serverless*, tanpa
state/file persisten antar-request). Jadi ada 3 penyesuaian mendasar:

| Bagian PHP lama | Versi Next.js ini |
|---|---|
| `session_start()` + `$_SESSION` | Cookie sesi yang ditandatangani (HMAC) — lihat `lib/auth.js` |
| Query manual di `views/*.php` | Server Components (`app/**/page.js`) yang fetch data langsung dari DB |
| Form `POST` ke `index.php?page=...` | Next.js **Server Actions** (`actions.js` di tiap folder halaman) |
| `$_SESSION['flash']` | Query string `?status=...&message=...` setelah redirect (`lib/flash.js`) |
| MySQL lokal (XAMPP) | Database MySQL-compatible eksternal (lihat bagian Database) |

Struktur folder sengaja dibuat mirip dengan `models/` PHP asli (`lib/models/authModel.js`,
`attendanceModel.js`, `dashboardModel.js`, `studentModel.js`) supaya gampang dibandingkan saat
presentasi/laporan.

## 1. Siapkan database (MySQL-compatible, gratis)

PlanetScale sudah menghapus paket gratisnya, jadi pakai salah satu ini:

- **TiDB Cloud Starter (rekomendasi)** — https://tidbcloud.com — gratis, wire-protocol MySQL, tanpa kartu kredit.
- **Aiven for MySQL** — https://aiven.io/free-mysql-database — always-free, 1GB RAM/storage.

Setelah database dibuat, catat host, port, username, password, dan nama database-nya.

Import skema: buka SQL console/editor dari dashboard provider tersebut, lalu jalankan isi file
`database/schema.sql` (sudah termasuk data dummy dosen + mahasiswa untuk uji coba).

> Akun demo (sama seperti versi PHP): email `firansyah@univ.ac.id`, password `password`.

## 2. Atur environment variables

Salin `.env.example` jadi `.env.local` untuk development lokal, isi sesuai kredensial database kamu:

```
DB_HOST=...
DB_PORT=4000
DB_USER=...
DB_PASSWORD=...
DB_NAME=...
DB_SSL=true
SESSION_SECRET=...   # generate: openssl rand -base64 48
```

## 3. Jalankan lokal (opsional, untuk uji coba dulu)

```bash
npm install
npm run dev
```

Buka `http://localhost:3000`.

## 4. Deploy ke Vercel

1. Push folder ini ke repo GitHub (atau GitLab/Bitbucket).
2. Buka https://vercel.com/new, import repo tersebut.
3. Di step **Environment Variables**, masukkan 6 variable yang sama seperti di `.env.example`
   (`DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASSWORD`, `DB_NAME`, `DB_SSL`, `SESSION_SECRET`).
4. Klik **Deploy**. Vercel otomatis mendeteksi ini sebagai project Next.js — tidak perlu konfigurasi build tambahan.

Atau lewat CLI:

```bash
npm i -g vercel
vercel
vercel env add DB_HOST
vercel env add DB_PORT
vercel env add DB_USER
vercel env add DB_PASSWORD
vercel env add DB_NAME
vercel env add DB_SSL
vercel env add SESSION_SECRET
vercel --prod
```

## Struktur project

```
app/
├── layout.js                 # root layout (font, CSS)
├── page.js                   # redirect ke /login atau /dashboard
├── not-found.js               # halaman 404
├── login/
│   ├── page.js
│   └── actions.js             # Server Action: login
└── (app)/                    # route group terproteksi (butuh login)
    ├── layout.js               # cek sesi + render sidebar/header
    ├── dashboard/page.js
    ├── absensi/{page.js,actions.js}
    ├── rekap/page.js
    └── mahasiswa/{page.js,actions.js}
lib/
├── db.js                      # koneksi pool mysql2
├── auth.js                    # sesi login (cookie ber-signature)
├── flash.js                   # flash message lewat query string
├── actions.js                  # Server Action: logout
└── models/                    # port dari models/*.php
components/                   # bagian interaktif (client components)
database/schema.sql            # skema + data dummy (tanpa CREATE DATABASE/USE)
```

## Catatan migrasi

- Password mahasiswa/dosen tetap pakai bcrypt (`bcryptjs`), hash lama dari `database.sql` kompatibel langsung.
- Fitur toggle dark mode & sidebar mobile di-port persis dari `public/js/app.js` ke `components/AppShell.js`.
- Style asli (`public/css/app.css`) dipindah ke `app/globals.css` tanpa perubahan class, jadi tampilannya identik.
- Folder `absensi_v2/` (file mentah `.frm`/`.ibd`) dari project asli **tidak dipakai** — itu file storage
  MySQL yang tidak portable. Yang dipakai adalah `database.sql` (dump SQL biasa) yang sudah dikonversi jadi `database/schema.sql`.
