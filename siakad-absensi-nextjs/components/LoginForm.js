'use client';

import { useFormState, useFormStatus } from 'react-dom';
import { useState } from 'react';
import { loginAction } from '../app/login/actions';

function SubmitButton() {
  const { pending } = useFormStatus();
  return (
    <button className="btn-primary w-full" type="submit" disabled={pending}>
      <i className="fas fa-right-to-bracket" />
      {pending ? 'Memproses...' : 'Masuk'}
    </button>
  );
}

export default function LoginForm() {
  const [state, formAction] = useFormState(loginAction, { error: null });
  const [showPassword, setShowPassword] = useState(false);

  return (
    <form action={formAction} className="space-y-5">
      {state?.error && (
        <div className="flash-message flash-error">
          <i className="fas fa-circle-exclamation" />
          <span>{state.error}</span>
        </div>
      )}

      <div>
        <label className="form-label">Email</label>
        <div className="form-icon-wrap">
          <i className="far fa-envelope" />
          <input
            className="form-input pl-11"
            type="email"
            name="email"
            required
            placeholder="firansyah@univ.ac.id"
            autoComplete="email"
          />
        </div>
      </div>

      <div>
        <label className="form-label">Password</label>
        <div className="form-icon-wrap relative">
          <i className="fas fa-lock" />
          <input
            className="form-input px-11"
            type={showPassword ? 'text' : 'password'}
            name="password"
            required
            placeholder="Masukkan password"
            autoComplete="current-password"
          />
          <button
            className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700"
            type="button"
            onClick={() => setShowPassword((v) => !v)}
            aria-label="Tampilkan password"
          >
            <i className={showPassword ? 'far fa-eye-slash' : 'far fa-eye'} />
          </button>
        </div>
      </div>

      <SubmitButton />
    </form>
  );
}
