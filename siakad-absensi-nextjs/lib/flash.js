/** Baca flash message dari searchParams halaman (?status=success&message=...). */
export function parseFlash(searchParams) {
  if (!searchParams?.status || !searchParams?.message) return null;
  return { type: searchParams.status, message: searchParams.message };
}

/** Bangun query string flash untuk ditambahkan ke URL redirect. */
export function flashQuery(type, message) {
  return `status=${encodeURIComponent(type)}&message=${encodeURIComponent(message)}`;
}
