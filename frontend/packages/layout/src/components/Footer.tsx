import React from 'react';
import { Config } from '@sodars/config';
import { useTenantStore, useAuthStore } from '@sodars/store';

export const Footer: React.FC = () => {
  const { activeOrganization } = useTenantStore();
  const { token } = useAuthStore();

  return (
    <footer className="h-10 border-t border-slate-200 dark:border-slate-800 bg-surface dark:bg-slate-900 px-6 flex items-center justify-between text-[11px] text-text-secondary select-none">
      <div className="flex items-center space-x-3">
        <span>© 2026 {Config.brand.name}</span>
        <span>•</span>
        <span className="capitalize">Env: {Config.environment.mode}</span>
        {activeOrganization && (
          <>
            <span>•</span>
            <span>Org: {activeOrganization.name}</span>
          </>
        )}
      </div>
      <div className="flex items-center space-x-3">
        <span className="flex items-center">
          <span className={`w-1.5 h-1.5 rounded-full mr-1.5 ${token ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'}`} />
          {token ? 'Realtime Connected' : 'Offline'}
        </span>
        <span>•</span>
        <span>FE: v{Config.versions.frontend}</span>
        <span>•</span>
        <span>BE: v{Config.versions.backend}</span>
      </div>
    </footer>
  );
};
export default Footer;
