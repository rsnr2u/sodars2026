import React from 'react';
import { Sidebar } from '../components/Sidebar';
import { Topbar } from '../components/Topbar';
import { Footer } from '../components/Footer';
import { Breadcrumbs } from '../components/Breadcrumbs';
import { CommandPalette } from '../components/CommandPalette';
import { ShortcutsHelpDialog } from '../components/ShortcutsHelpDialog';

export const AdminLayout: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  return (
    <div className="flex min-h-screen bg-background dark:bg-slate-950 font-sans text-text-primary transition-colors duration-200">
      {/* 1. Collapsible / Responsive Sidebar */}
      <Sidebar />

      {/* 2. Main Portal shell */}
      <div className="flex-1 flex flex-col min-w-0">
        <Topbar />
        
        {/* Dynamic content canvas */}
        <main className="flex-1 p-6 overflow-y-auto space-y-4">
          <Breadcrumbs />
          <div className="bg-surface dark:bg-slate-900 border border-border dark:border-slate-800 rounded-lg p-6 shadow-sm">
            {children}
          </div>
        </main>
        
        <Footer />
      </div>

      {/* Global overlays */}
      <CommandPalette />
      <ShortcutsHelpDialog />
    </div>
  );
};
export default AdminLayout;
