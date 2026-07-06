'use client';

export default function ConfirmForm({ action, confirmMessage, className, children }) {
  return (
    <form
      action={action}
      className={className}
      onSubmit={(e) => {
        if (!window.confirm(confirmMessage || 'Lanjutkan aksi ini?')) {
          e.preventDefault();
        }
      }}
    >
      {children}
    </form>
  );
}
