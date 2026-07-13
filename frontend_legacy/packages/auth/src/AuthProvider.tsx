import React, { useEffect } from 'react';
import { IdentityContext } from './IdentityContext';
import { identity } from './Identity';
import { SessionManager } from './SessionManager';

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  useEffect(() => {
    SessionManager.initialize();
  }, []);

  return React.createElement(
    IdentityContext.Provider,
    { value: identity },
    children
  );
};
