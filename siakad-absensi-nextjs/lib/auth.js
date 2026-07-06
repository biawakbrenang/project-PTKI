import crypto from 'crypto';
import { cookies } from 'next/headers';

const COOKIE_NAME = 'siakad_session';
const MAX_AGE = 60 * 60 * 24; // 24 jam, sama seperti cookie_lifetime di config.php lama

function getSecret() {
  const secret = process.env.SESSION_SECRET;
  if (!secret) {
    throw new Error('SESSION_SECRET belum diatur di environment variables.');
  }
  return secret;
}

function sign(value) {
  return crypto.createHmac('sha256', getSecret()).update(value).digest('hex');
}

/** Bungkus payload sesi (id dosen, nama, dll) jadi cookie yang ditandatangani. */
export function encodeSession(payload) {
  const json = Buffer.from(JSON.stringify(payload), 'utf8').toString('base64url');
  const signature = sign(json);
  return `${json}.${signature}`;
}

/** Verifikasi & buka cookie sesi. Mengembalikan null kalau tidak valid/kedaluwarsa. */
export function decodeSession(cookieValue) {
  if (!cookieValue) return null;
  const [json, signature] = cookieValue.split('.');
  if (!json || !signature) return null;

  const expected = sign(json);
  const validSignature =
    signature.length === expected.length &&
    crypto.timingSafeEqual(Buffer.from(signature), Buffer.from(expected));

  if (!validSignature) return null;

  try {
    return JSON.parse(Buffer.from(json, 'base64url').toString('utf8'));
  } catch {
    return null;
  }
}

/** Dipanggil dari Server Action setelah login berhasil. */
export function createSession(payload) {
  cookies().set(COOKIE_NAME, encodeSession(payload), {
    httpOnly: true,
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'lax',
    path: '/',
    maxAge: MAX_AGE,
  });
}

/** Dipanggil dari Server Action saat logout. */
export function destroySession() {
  cookies().delete(COOKIE_NAME);
}

/** Baca sesi dosen yang sedang login (dipakai di Server Component / layout). */
export function getSession() {
  const cookieValue = cookies().get(COOKIE_NAME)?.value;
  return decodeSession(cookieValue);
}
