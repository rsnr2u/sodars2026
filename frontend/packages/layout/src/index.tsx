import React, { createContext, useContext } from 'react';
import { useAuthStore, useTenantStore, useSidebarStore } from '@sodars/store';
import { NavigationRegistry } from '@sodars/sdk';

export * from './types/RouteParams';
export * from './types/RouteMeta';

// 1. Shell Provider Context
interface ShellContextType {
  sidebarOpen: boolean;
  toggleSidebar: () => void;
}

const ShellContext = createContext<ShellContextType | undefined>(undefined);

export const ShellProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { isOpen, toggle } = useSidebarStore();

  return React.createElement(ShellContext.Provider, {
    value: { sidebarOpen: isOpen, toggleSidebar: toggle }
  }, children);
};

export const useShell = () => {
  const ctx = useContext(ShellContext);
  if (!ctx) throw new Error('useShell must be used inside a ShellProvider.');
  return ctx;
};

// 2. Breadcrumbs Component
export const Breadcrumbs: React.FC = () => {
  return (
    <nav className="text-xs font-medium text-slate-500 flex items-center space-x-2">
      <span className="hover:text-slate-800 cursor-pointer">Control Center</span>
      <span>/</span>
      <span className="text-slate-800">Dashboard</span>
    </nav>
  );
};

// 3. Topbar Component
export const Topbar: React.FC = () => {
  const { user } = useAuthStore();
  const { activeOrganization, setActiveOrganization } = useTenantStore();

  return (
    <header className="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between shadow-sm">
      <div className="flex items-center space-x-6">
        <Breadcrumbs />
        <div className="h-4 w-px bg-slate-200" />
        <div className="flex items-center space-x-2">
          <span className="text-xs text-slate-400 font-bold uppercase">Org:</span>
          <select
            value={activeOrganization?.id ?? ''}
            onChange={(e) => {
              if (e.target.value === '') {
                setActiveOrganization(null);
              } else {
                setActiveOrganization({
                  id: e.target.value,
                  name: 'Operations Corp',
                  slug: 'ops'
                });
              }
            }}
            className="px-2 py-1 text-xs border border-slate-300 rounded bg-slate-50 font-semibold text-slate-700 focus:outline-none focus:ring-1 focus:ring-indigo-500"
          >
            <option value="">Select Account...</option>
            <option value="org-999-id">Operations Corp</option>
          </select>
        </div>
      </div>
      <div className="flex items-center space-x-4">
        <div className="text-xs font-semibold text-slate-600">{user?.name ?? 'Admin'}</div>
        <div className="w-8 h-8 rounded-full bg-indigo-500 text-white flex items-center justify-center font-bold text-sm">
          A
        </div>
      </div>
    </header>
  );
};

// 4. Sidebar Component
export const Sidebar: React.FC = () => {
  const { clearSession } = useAuthStore();
  const menuItems = NavigationRegistry.getFlatList();

  const handleLogout = () => {
    clearSession();
    window.location.href = '/login';
  };

  return (
    <aside className="w-64 bg-slate-900 text-slate-300 flex flex-col border-r border-slate-800 select-none">
      <div className="p-4 border-b border-slate-800 flex items-center justify-between">
        <div>
          <h1 className="text-lg font-bold text-white tracking-wider">SODARS</h1>
          <span className="text-[10px] text-slate-500 font-semibold uppercase tracking-widest">Monolith v1.1</span>
        </div>
      </div>
      <nav className="flex-1 p-4 space-y-1.5">
        {menuItems.map((item: any) => {
          const isActive = window.location.pathname === item.route;
          return (
            <a
              key={item.id}
              href={item.route}
              className={`flex items-center space-x-3 px-3 py-2 rounded text-sm transition-all duration-150 ${
                isActive
                  ? 'bg-indigo-600 text-white font-semibold shadow-sm'
                  : 'hover:bg-slate-800 hover:text-white'
              }`}
            >
              <span>{item.title}</span>
            </a>
          );
        })}
      </nav>
      <div className="p-4 border-t border-slate-800 flex flex-col space-y-2">
        <button
          onClick={handleLogout}
          className="text-xs text-left text-red-400 hover:text-red-300 font-medium transition-colors cursor-pointer"
        >
          Sign Out Portal
        </button>
      </div>
    </aside>
  );
};

// 5. Footer Component
export const Footer: React.FC = () => {
  return (
    <footer className="h-10 border-t border-slate-200 bg-white px-6 flex items-center justify-between text-[11px] text-slate-400">
      <div>© 2026 SODARS Monolith. All rights reserved.</div>
      <div className="flex items-center space-x-3">
        <span>Status: Online</span>
        <span>•</span>
        <span>Version 1.1.1</span>
      </div>
    </footer>
  );
};

// 6. Shell Layout Component
export const ShellLayout: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  return (
    <div className="flex min-h-screen bg-slate-100 font-sans">
      <Sidebar />
      <div className="flex-1 flex flex-col">
        <Topbar />
        <main className="flex-1 p-6 overflow-y-auto">
          {children}
        </main>
        <Footer />
      </div>
    </div>
  );
};

// 7. Page Container Component
export const PageContainer: React.FC<{ title: string; children: React.ReactNode }> = ({
  title,
  children
}) => {
  return (
    <div className="bg-white border border-slate-200 rounded-lg shadow-sm p-6 space-y-4">
      <h2 className="text-xl font-bold tracking-tight text-slate-900 border-b border-slate-100 pb-3">{title}</h2>
      <div>{children}</div>
    </div>
  );
};
