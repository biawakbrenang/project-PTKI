import React, { useState, useEffect } from 'react';
import { loadEcoState, saveEcoState, Role, User, getInitialState, ALL_USERS } from './types';
import LoginPortal from './components/LoginPortal';
import LecturerDashboard from './components/LecturerDashboard';
import StudentDashboard from './components/StudentDashboard';
import AdminDashboard from './components/AdminDashboard';

export default function App() {
  const [state, setState] = useState(() => loadEcoState());

  // Automatically save state whenever it changes
  useEffect(() => {
    saveEcoState(state);
  }, [state]);

  const handleLogin = (role: Role, userIdentifier: string) => {
    setState(prev => {
      // Find the user from the state users list
      const selectedUser = prev.users.find(u => u.identifier === userIdentifier) || prev.users[1]; // fallback to Alex Rivera if not found
      return {
        ...prev,
        currentUser: selectedUser,
        currentRole: role
      };
    });
  };

  const handleLogout = () => {
    setState(prev => ({
      ...prev,
      currentUser: null,
      currentRole: null
    }));
  };

  return (
    <div className="relative min-h-screen bg-slate-50 text-slate-900 selection:bg-emerald-600 selection:text-white">
      
      {/* RENDER ACTIVE ROUTE VIEW */}
      {state.currentRole === null ? (
        <LoginPortal users={state.users} onLogin={handleLogin} />
      ) : (
        <>
          {state.currentRole === 'lecturer' && (
            <LecturerDashboard 
              state={state} 
              setState={setState} 
              onLogout={handleLogout} 
            />
          )}

          {state.currentRole === 'student' && (
            <StudentDashboard 
              state={state} 
              setState={setState} 
              onLogout={handleLogout} 
            />
          )}

          {state.currentRole === 'admin' && (
            <AdminDashboard 
              state={state} 
              setState={setState} 
              onLogout={handleLogout} 
            />
          )}
        </>
      )}

    </div>
  );
}
