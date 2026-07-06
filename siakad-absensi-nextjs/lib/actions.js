'use server';

import { redirect } from 'next/navigation';
import { destroySession } from './auth';

export async function logoutAction() {
  destroySession();
  redirect('/login');
}
