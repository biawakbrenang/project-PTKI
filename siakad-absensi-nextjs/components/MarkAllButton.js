'use client';

export default function MarkAllButton({ status, children }) {
  const handleClick = () => {
    document
      .querySelectorAll(`input[type="radio"][value="${status}"]`)
      .forEach((input) => {
        input.checked = true;
      });
  };

  return (
    <button type="button" className="btn-muted" onClick={handleClick}>
      {children}
    </button>
  );
}
