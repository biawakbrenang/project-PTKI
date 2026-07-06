'use client';

export default function AutoSubmitSelect({ children, ...props }) {
  return (
    <select
      {...props}
      onChange={(e) => {
        props.onChange?.(e);
        e.currentTarget.form?.requestSubmit();
      }}
    >
      {children}
    </select>
  );
}
