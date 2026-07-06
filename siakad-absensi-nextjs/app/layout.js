import './globals.css';

export const metadata = {
  title: 'Sistem Absensi Dosen',
  description: 'SIAKAD V2 - Sistem Absensi Dosen',
};

export default function RootLayout({ children }) {
  return (
    <html lang="id">
      <head>
        <link
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
          rel="stylesheet"
        />
      </head>
      <body className="bg-slate-100 text-slate-800">{children}</body>
    </html>
  );
}
