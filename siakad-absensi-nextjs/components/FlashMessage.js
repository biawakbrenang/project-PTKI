export default function FlashMessage({ flash }) {
  if (!flash) return null;
  const isSuccess = flash.type === 'success';

  return (
    <div className={`mb-6 flash-message ${isSuccess ? 'flash-success' : 'flash-error'}`}>
      <i className={`fas ${isSuccess ? 'fa-circle-check' : 'fa-circle-exclamation'}`} />
      <span>{flash.message}</span>
    </div>
  );
}
