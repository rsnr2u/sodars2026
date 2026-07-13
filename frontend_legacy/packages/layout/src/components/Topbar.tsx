import React from 'react';
import { useAuthStore, useTenantStore, useThemeStore, ThemeMode } from '@sodars/store';
import { EventBus } from '@sodars/events';

export const Topbar: React.FC = () => {
  const { user } = useAuthStore();
  const { activeOrganization, setActiveOrganization, setActiveBranch } = useTenantStore();
  const { theme, setTheme } = useThemeStore();

  const handleOrganizationChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    const val = e.target.value;
    if (val === '') {
      setActiveOrganization(null);
      setActiveBranch(null);
    } else {
      const org = user?.organizations?.find(o => o.id === val);
      if (org) {
        setActiveOrganization(org);
        EventBus.publish('auth:tenant-changed', org);
      }
    }
  };

  const toggleTheme = () => {
    if (theme === ThemeMode.Light) setTheme(ThemeMode.Dark);
    else if (theme === ThemeMode.Dark) setTheme(ThemeMode.System);
    else setTheme(ThemeMode.Light);
  };

  return (
    <header className="h-14 border-b border-slate-200 dark:border-slate-800 bg-surface dark:bg-slate-900 px-6 flex items-center justify-between select-none">
      <div className="flex items-center space-x-4">
        {/* Organization Switcher selector */}
        {user?.organizations && user.organizations.length > 0 && (
          <div className="flex items-center space-x-2">
            <label className="text-[10px] uppercase font-bold tracking-wider text-text-secondary">Context</label>
            <select
              value={activeOrganization?.id ?? ''}
              onChange={handleOrganizationChange}
              className="px-2.5 py-1 text-xs border border-border dark:border-slate-800 rounded bg-background dark:bg-slate-950 font-semibold text-text-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
              <option value="">Select Account...</option>
              {user.organizations.map(org => (
                <option key={org.id} value={org.id}>{org.name}</option>
              ))}
            </select>
          </div>
        )}
      </div>

      <div className="flex items-center space-x-4">
        {/* Theme Toggle Button switcher */}
        <button
          onClick={toggleTheme}
          className="p-1.5 rounded-md hover:bg-surface-hover text-text-secondary transition-colors cursor-pointer text-xs uppercase font-bold tracking-wider border border-border dark:border-slate-800"
        >
          Theme: {theme}
        </button>

        <div className="flex items-center space-x-3">
          <div className="text-xs font-semibold text-text-primary">{user?.name ?? 'Guest'}</div>
          <div className="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center font-bold text-sm">
            {user?.name?.charAt(0) ?? 'G'}
          </div>
        </div>
      </div>
    </header>
  );
};
export default Topbar;
