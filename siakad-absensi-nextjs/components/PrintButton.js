'use client';

export default function PrintButton() {
  return (
    <button
      type="button"
      className="btn-muted justify-center"
      onClick={() => window.print()}
    >
      <i className="fas fa-print" />
      Cetak
    </button>
  );
}
