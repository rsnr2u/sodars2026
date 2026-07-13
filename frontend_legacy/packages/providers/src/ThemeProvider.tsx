import React, { useEffect, createContext, useContext } from 'react';
import { useThemeStore, ThemeMode } from '@sodars/store';
import { EventBus } from '@sodars/events';

interface ThemeContextType {
  theme: ThemeMode;
  setTheme: (theme: ThemeMode) => void;
}

const ThemeContext = createContext<ThemeContextType | null>(null);

export const ThemeProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { theme, setTheme } = useThemeStore();

  useEffect(() => {
    const root = window.document.documentElement;

    const applyTheme = (mode: ThemeMode) => {
      root.classList.remove('light', 'dark');

      if (mode === ThemeMode.System) {
        const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches
          ? 'dark'
          : 'light';
        root.classList.add(systemTheme);
        EventBus.publish('theme:changed', systemTheme);
      } else {
        root.classList.add(mode);
        EventBus.publish('theme:changed', mode);
      }
    };

    applyTheme(theme);

    // Watch system changes if in system mode
    if (theme === ThemeMode.System) {
       const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
       const listener = () => applyTheme(ThemeMode.System);
       mediaQuery.addEventListener('change', listener);
       return () => {
         mediaQuery.removeEventListener('change', listener);
       };
    }
    return () => {};
  }, [theme]);

  return React.createElement(
    ThemeContext.Provider,
    { value: { theme, setTheme } },
    children
  );
};

export const useTheme = () => {
  const context = useContext(ThemeContext);
  if (!context) {
    throw new Error('useTheme must be used within a ThemeProvider');
  }
  return context;
};
