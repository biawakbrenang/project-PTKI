import { redirect } from 'next/navigation';
import { getSession } from '../../lib/auth';
import AppShell from '../../components/AppShell';

export default function AppLayout({ children }) {
  const session = getSession();
  if (!session) {
    redirect('/login');
  }

  return <AppShell session={session}>{children}</AppShell>;
}
