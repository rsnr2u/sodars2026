import { createRootRoute, Outlet, Link } from '@tanstack/react-router';
import { NavigationRegistry } from '@sodars/sdk';
import { useAuthStore, useTenantStore } from '@sodars/auth';

export const Route = createRootRoute({
  component: RootComponent,
});

function RootComponent() {
  const { user, clearSession } = useAuthStore();
  const { activeOrganization, setActiveOrganization } = useTenantStore();
  const menuItems = NavigationRegistry.getItems();

  const handleLogout = () => {
    clearSession();
    window.location.href = '/login';
  };

  return (
    <div className="flex min-h-screen bg-slate-50 text-slate-900">
      {/* Sidebar Panel */}
      <aside className="w-64 bg-slate-900 text-white flex flex-col">
        <div className="p-4 border-b border-slate-800">
          <h1 className="text-xl font-bold tracking-wider text-indigo-400">SODARS ERP</h1>
          <span className="text-xs text-slate-400">Operations Control</span>
        </div>
        <nav className="flex-1 p-4 space-y-2">
          {menuItems.map((item) => (
            <Link
              key={item.id}
              to={item.path}
              className="flex items-center space-x-3 px-3 py-2 rounded transition-colors hover:bg-slate-800 text-slate-300 hover:text-white"
              activeProps={{ className: 'bg-indigo-600 text-white font-medium' }}
            >
              <span className="text-sm">{item.label}</span>
            </Link>
          ))}
        </nav>
        <div className="p-4 border-t border-slate-800">
          <div className="text-sm font-semibold truncate">{user?.name ?? 'Guest User'}</div>
          <div className="text-xs text-slate-400 truncate mb-3">{user?.email ?? 'anonymous@sodars.com'}</div>
          <button
            onClick={handleLogout}
            className="w-full text-left text-xs text-red-400 hover:text-red-300 font-medium transition-colors"
          >
            Sign Out
          </button>
        </div>
      </aside>

      {/* Main Content Area */}
      <div className="flex-1 flex flex-col">
        <header className="h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between">
          <div className="flex items-center space-x-4">
            <span className="text-sm font-medium text-slate-500">Tenant Context:</span>
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
              className="px-3 py-1.5 border border-slate-300 rounded text-sm bg-slate-50 font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
              <option value="">Select Organization...</option>
              <option value="org-999-id">Operations Corp</option>
            </select>
          </div>
          <div className="flex items-center space-x-4">
            <div className="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center font-bold text-indigo-700">
              U
            </div>
          </div>
        </header>
        <main className="flex-1 p-6 overflow-y-auto">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
