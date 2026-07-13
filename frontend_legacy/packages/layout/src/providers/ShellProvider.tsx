import React, { createContext, useContext, useState, useEffect } from 'react';
import { useSidebarStore } from '@sodars/store';

interface ShellContextType {
  sidebarOpen: boolean;
  toggleSidebar: () => void;
  setSidebarOpen: (open: boolean) => void;
  mobileDrawerOpen: boolean;
  setMobileDrawerOpen: (open: boolean) => void;
  commandPaletteOpen: boolean;
  setCommandPaletteOpen: (open: boolean) => void;
  shortcutsDialogOpen: boolean;
  setShortcutsDialogOpen: (open: boolean) => void;
}

const ShellContext = createContext<ShellContextType | null>(null);

export const ShellProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { isOpen: sidebarOpen, toggle: toggleSidebar, setOpen: setSidebarOpen } = useSidebarStore();
  const [mobileDrawerOpen, setMobileDrawerOpen] = useState(false);
  const [commandPaletteOpen, setCommandPaletteOpen] = useState(false);
  const [shortcutsDialogOpen, setShortcutsDialogOpen] = useState(false);

  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      // 1. Command Palette: Ctrl + K
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        setCommandPaletteOpen((prev) => !prev);
      }
      
      // 2. Collapse Sidebar: Alt + [
      if (e.altKey && e.key === '[') {
        e.preventDefault();
        setSidebarOpen(false);
      }
      
      // 3. Expand Sidebar: Alt + ]
      if (e.altKey && e.key === ']') {
        e.preventDefault();
        setSidebarOpen(true);
      }

      // 4. Focus Global Search: /
      if (e.key === '/' && document.activeElement?.tagName !== 'INPUT' && document.activeElement?.tagName !== 'TEXTAREA') {
        e.preventDefault();
        // Custom search trigger or focused target lookup
        console.log('[ShellProvider] Focus search field...');
      }

      // 5. Help / Shortcuts Cheat Sheet: ? (Shift + /)
      if (e.key === '?' && document.activeElement?.tagName !== 'INPUT' && document.activeElement?.tagName !== 'TEXTAREA') {
        e.preventDefault();
        setShortcutsDialogOpen((prev) => !prev);
      }
    };

    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, [setSidebarOpen]);

  return React.createElement(
    ShellContext.Provider,
    {
      value: {
        sidebarOpen,
        toggleSidebar,
        setSidebarOpen,
        mobileDrawerOpen,
        setMobileDrawerOpen,
        commandPaletteOpen,
        setCommandPaletteOpen,
        shortcutsDialogOpen,
        setShortcutsDialogOpen,
      },
    },
    children
  );
};

export const useShell = () => {
  const context = useContext(ShellContext);
  if (!context) {
    throw new Error('useShell must be used within a ShellProvider');
  }
  return context;
};
