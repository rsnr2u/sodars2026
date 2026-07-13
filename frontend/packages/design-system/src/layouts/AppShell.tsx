import React, { useState, useEffect } from 'react';
import { Logo } from '@sodars/branding';
import { EntityIcons, NavigationIcons, ActionIcons } from '@sodars/icons';
import { ThemeRegistry, NavigationRegistry, NotificationRegistry } from '@sodars/core';

export interface AppShellProps {
  children: React.ReactNode;
}

export function AppShell({ children }: AppShellProps) {
  const [theme, setTheme] = useState(ThemeRegistry.getTheme());
  const [isSidebarOpen, setIsSidebarOpen] = useState(true);
  const [searchQuery, setSearchQuery] = useState('');
  const [showCommandPalette, setShowCommandPalette] = useState(false);
  const [showNotificationDrawer, setShowNotificationDrawer] = useState(false);
  const [activeItem, setActiveItem] = useState('providers-directory');

  useEffect(() => {
    const unsub = ThemeRegistry.subscribe(newTheme => setTheme(newTheme));
    return () => { unsub(); };
  }, []);

  // Keyboard listener for Ctrl+K
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        setShowCommandPalette(prev => !prev);
      }
    };
    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, []);

  const sidebarItems = NavigationRegistry.getSidebarItems();

  const handleThemeChange = (mode: 'light' | 'dark' | 'high-contrast') => {
    ThemeRegistry.setTheme(mode);
    if (typeof window !== 'undefined') {
      const root = document.documentElement;
      root.classList.remove('dark', 'high-contrast');
      if (mode === 'dark') root.classList.add('dark');
      if (mode === 'high-contrast') root.classList.add('high-contrast');
    }
  };

  const notifications = NotificationRegistry.getAll();

  return (
    <div className={`flex h-screen w-screen overflow-hidden bg-background text-text-primary font-body ${theme}`}>
      {/* Sidebar Container */}
      <aside
        className={`fixed inset-y-0 left-0 z-40 flex flex-col border-r border-border bg-surface transition-transform duration-300 ${
          isSidebarOpen ? 'w-64 translate-x-0' : 'w-0 -translate-x-full lg:w-20 lg:translate-x-0'
        }`}
      >
        {/* Brand Header */}
        <div className="flex h-16 items-center justify-between px-6 border-b border-border">
          {isSidebarOpen ? (
            <Logo />
          ) : (
            <div className="mx-auto text-primary font-extrabold text-xl">S</div>
          )}
          <button
            onClick={() => setIsSidebarOpen(!isSidebarOpen)}
            className="rounded p-1 text-text-muted hover:bg-surface-hover hover:text-text-primary transition-colors cursor-pointer"
          >
            <NavigationIcons.Menu className="h-5 w-5" />
          </button>
        </div>

        {/* Sidebar Nav Items */}
        <nav className="flex-1 space-y-1.5 px-3 py-4 overflow-y-auto">
          {/* Workspace Title Group */}
          {isSidebarOpen && (
            <div className="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-text-muted">
              Workspaces
            </div>
          )}
          {sidebarItems.map(item => {
            const IconComponent = EntityIcons.Providers; // Map dynamically
            const isActive = activeItem === item.id;

            return (
              <button
                key={item.id}
                onClick={() => setActiveItem(item.id)}
                className={`flex w-full items-center gap-3 px-3 py-2.5 rounded-md text-sm font-semibold transition-colors cursor-pointer ${
                  isActive
                    ? 'bg-primary text-white shadow-sm'
                    : 'text-text-secondary hover:bg-surface-hover hover:text-text-primary'
                }`}
              >
                <IconComponent className="h-5 w-5 flex-shrink-0" />
                {isSidebarOpen && <span>{item.label}</span>}
              </button>
            );
          })}
        </nav>

        {/* Sidebar Footer User Info */}
        <div className="p-4 border-t border-border flex items-center gap-3">
          <div className="h-9 w-9 rounded-full bg-primary-light flex items-center justify-center text-primary font-extrabold text-sm border border-primary/10">
            IN
          </div>
          {isSidebarOpen && (
            <div className="flex-1 min-w-0">
              <div className="text-sm font-semibold text-text-primary truncate">India Network</div>
              <div className="text-xs text-text-muted truncate">admin@sodaars.com</div>
            </div>
          )}
        </div>
      </aside>

      {/* Main Main-Frame Area */}
      <div className={`flex-1 flex flex-col overflow-hidden min-h-screen transition-all duration-300 ${
        isSidebarOpen ? 'lg:pl-64' : 'lg:pl-20'
      }`}>
        {/* Topbar navigation panel */}
        <header className="flex h-16 items-center justify-between px-6 border-b border-border bg-surface sticky top-0 z-20">
          <div className="flex items-center gap-4 flex-1">
            {/* Search Bar Input (Triggers Command Palette overlay) */}
            <div
              onClick={() => setShowCommandPalette(true)}
              className="relative w-full max-w-sm cursor-pointer group"
            >
              <ActionIcons.Search className="absolute left-3 top-2.5 h-4 w-4 text-text-muted group-hover:text-text-primary transition-colors" />
              <div className="w-full pl-9 pr-4 py-2 border border-border rounded-md text-sm bg-background text-text-muted hover:border-text-secondary transition-colors flex items-center justify-between">
                <span>Search SODAARS...</span>
                <kbd className="hidden sm:inline-flex h-5 select-none items-center gap-0.5 rounded border border-border bg-surface px-1.5 font-mono text-[10px] font-medium text-text-muted">
                  Ctrl K
                </kbd>
              </div>
            </div>
          </div>

          <div className="flex items-center gap-3">
            {/* Theme Preference selectors Toggle Switch */}
            <div className="flex border border-border rounded-md p-0.5 bg-background">
              <button
                onClick={() => handleThemeChange('light')}
                className={`p-1.5 rounded transition-colors text-xs font-semibold cursor-pointer ${
                  theme === 'light' ? 'bg-surface text-primary shadow-sm' : 'text-text-muted hover:text-text-primary'
                }`}
                title="Light Mode"
              >
                Light
              </button>
              <button
                onClick={() => handleThemeChange('dark')}
                className={`p-1.5 rounded transition-colors text-xs font-semibold cursor-pointer ${
                  theme === 'dark' ? 'bg-surface text-primary shadow-sm' : 'text-text-muted hover:text-text-primary'
                }`}
                title="Dark Mode"
              >
                Dark
              </button>
              <button
                onClick={() => handleThemeChange('high-contrast')}
                className={`p-1.5 rounded transition-colors text-xs font-semibold cursor-pointer ${
                  theme === 'high-contrast' ? 'bg-surface text-primary shadow-sm' : 'text-text-muted hover:text-text-primary'
                }`}
                title="High Contrast Mode"
              >
                Contrast
              </button>
            </div>

            {/* Notification Drawer trigger badge */}
            <button
              onClick={() => setShowNotificationDrawer(true)}
              className="relative p-2 text-text-secondary hover:bg-surface-hover hover:text-text-primary rounded-md transition-colors cursor-pointer"
            >
              <ActionIcons.Notification className="h-5 w-5" />
              {NotificationRegistry.getUnreadCount() > 0 && (
                <span className="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-danger ring-2 ring-surface" />
              )}
            </button>
          </div>
        </header>

        {/* Fluid page content viewport slot */}
        <main className="flex-1 overflow-y-auto bg-background p-6">
          <div className="mx-auto max-w-7xl h-full">
            {children}
          </div>
        </main>
      </div>

      {/* Raycast-style Command Palette dialog Modal Backdrop */}
      {showCommandPalette && (
        <div className="fixed inset-0 z-50 flex items-start justify-center pt-24 bg-slate-900/60 backdrop-blur-xs">
          <div className="relative w-full max-w-lg bg-surface border border-border rounded-xl shadow-2xl overflow-hidden animate-scale-up">
            {/* Input Search header */}
            <div className="flex items-center px-4 py-3 border-b border-border">
              <ActionIcons.Search className="h-5 w-5 text-text-muted mr-3" />
              <input
                type="text"
                placeholder="Search actions or workspaces..."
                value={searchQuery}
                onChange={e => setSearchQuery(e.target.value)}
                className="flex-1 bg-transparent border-none text-text-primary placeholder:text-text-muted focus:outline-none text-sm"
                autoFocus
              />
              <button
                onClick={() => setShowCommandPalette(false)}
                className="p-1 text-text-muted hover:bg-surface-hover hover:text-text-primary rounded cursor-pointer"
              >
                <NavigationIcons.Close className="h-4 w-4" />
              </button>
            </div>
            {/* Command actions lists */}
            <div className="max-h-72 overflow-y-auto p-2">
              <div className="text-[10px] font-bold text-text-muted uppercase px-3 py-1 tracking-wider">
                Workspaces Navigation
              </div>
              <button
                onClick={() => { setActiveItem('providers-directory'); setShowCommandPalette(false); }}
                className="flex w-full items-center justify-between px-3 py-2.5 rounded-md hover:bg-surface-hover text-sm text-text-secondary hover:text-text-primary cursor-pointer text-left"
              >
                <span>Navigate to Providers Workspace Directory</span>
                <kbd className="text-[10px] text-text-muted border border-border px-1.5 py-0.5 rounded">Enter</kbd>
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Right Drawer Slide-over notifications panel */}
      {showNotificationDrawer && (
        <div className="fixed inset-0 z-50 flex justify-end bg-slate-900/40 backdrop-blur-xs">
          <div className="w-80 bg-surface border-l border-border h-full shadow-2xl flex flex-col animate-slide-in">
            <div className="flex h-16 items-center justify-between px-6 border-b border-border bg-surface">
              <h3 className="font-semibold text-text-primary">Notifications</h3>
              <button
                onClick={() => setShowNotificationDrawer(false)}
                className="p-1 text-text-muted hover:bg-surface-hover hover:text-text-primary rounded cursor-pointer"
              >
                <NavigationIcons.Close className="h-5 w-5" />
              </button>
            </div>
            <div className="flex-1 overflow-y-auto p-4 space-y-3">
              {notifications.length === 0 ? (
                <div className="text-center text-text-muted py-8 text-sm">No new alerts or notifications.</div>
              ) : (
                notifications.map(item => (
                  <div key={item.id} className="p-3 border border-border rounded-lg bg-background hover:border-primary/20 transition-colors">
                    <div className="font-semibold text-xs text-text-primary mb-1">{item.title}</div>
                    <div className="text-xs text-text-muted">{item.body}</div>
                  </div>
                ))
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
export default AppShell;
