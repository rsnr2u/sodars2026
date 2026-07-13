import React from 'react';
import { QueryProvider } from '@sodars/query';
import { ShellProvider } from '@sodars/layout';
import { AuthProvider } from '@sodars/auth';
import { ThemeProvider, useTheme } from './ThemeProvider';

export const NotificationProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  return React.createElement(React.Fragment, null, children);
};

export const AppProviders: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  // Wrap layout, query, theme, auth, and notifications contexts in a nested tree structure
  return React.createElement(
    QueryProvider,
    null,
    React.createElement(
      AuthProvider,
      null,
      React.createElement(
        ThemeProvider,
        null,
        React.createElement(
          NotificationProvider,
          null,
          React.createElement(
            ShellProvider,
            null,
            children
          )
        )
      )
    )
  );
};
export { ShellProvider, QueryProvider, AuthProvider, ThemeProvider, useTheme };
