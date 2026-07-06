export default function NotFound() {
  return (
    <html lang="id">
      <head>
        <link
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
          rel="stylesheet"
        />
      </head>
      <body className="flex min-h-screen items-center justify-center bg-slate-100 text-slate-800">
        <div className="empty-state panel max-w-md p-10 text-center">
          <i className="fas fa-triangle-exclamation" />
          <h1 className="text-2xl font-black text-slate-900">404 - Halaman Tidak Ditemukan</h1>
          <p>Halaman yang kamu cari tidak tersedia.</p>
          <a href="/dashboard" className="btn-primary mt-2">
            <i className="fas fa-house" />
            Kembali ke Dashboard
          </a>
        </div>
      </body>
    </html>
  );
}
