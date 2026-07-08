import React from 'react';
import { useNavigation, NavigationNode } from '@sodars/sdk';
import { useSidebarStore, useAuthStore } from '@sodars/store';
import { SodarsIcon } from '@sodars/icons';

export const Sidebar: React.FC = () => {
  const { isOpen, toggle } = useSidebarStore();
  const { clearSession } = useAuthStore();
  const treeNodes = useNavigation();

  const handleLogout = () => {
    clearSession();
    window.location.href = '/login';
  };

  const renderNode = (node: NavigationNode) => {
    if (node.hidden) return null;

    const isActive = window.location.pathname === node.route;
    const hasChildren = node.children && node.children.length > 0;

    return (
      <div key={node.id} className="space-y-1">
        <a
          href={node.route || '#'}
          target={node.target}
          className={`flex items-center justify-between px-3 py-2 rounded text-sm transition-all duration-150 ${
            isActive
              ? 'bg-primary text-white font-semibold shadow-sm'
              : 'hover:bg-surface-hover text-text-secondary hover:text-text-primary'
          } ${node.disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'}`}
          onClick={(e) => {
            if (node.disabled) {
              e.preventDefault();
            }
          }}
        >
          <div className="flex items-center space-x-3">
            {node.icon && <SodarsIcon name={node.icon} size={16} />}
            {isOpen && <span>{node.title}</span>}
          </div>

          {isOpen && node.badge && (
            <span
              title={node.badge.tooltip}
              className={`px-1.5 py-0.5 text-[10px] font-bold rounded ${
                node.badge.variant === 'danger'
                  ? 'bg-danger text-white'
                  : node.badge.variant === 'success'
                  ? 'bg-success text-white'
                  : node.badge.variant === 'warning'
                  ? 'bg-warning text-black'
                  : 'bg-primary text-white'
              } ${node.badge.pulse ? 'animate-pulse' : ''}`}
            >
              {node.badge.value}
            </span>
          )}
        </a>

        {isOpen && hasChildren && (
          <div className="pl-6 space-y-1 border-l border-border dark:border-slate-800 ml-5">
            {node.children!.map(child => renderNode(child))}
          </div>
        )}
      </div>
    );
  };

  return (
    <aside
      className={`bg-surface dark:bg-slate-900 border-r border-border dark:border-slate-800 flex flex-col transition-all duration-200 select-none ${
        isOpen ? 'w-64' : 'w-16'
      }`}
    >
      <div className="p-4 border-b border-border dark:border-slate-800 flex items-center justify-between">
        {isOpen && (
          <div>
            <h1 className="text-sm font-bold text-text-primary tracking-wider uppercase">SODARS</h1>
            <span className="text-[9px] text-text-muted font-bold uppercase tracking-wider">Modular Platform</span>
          </div>
        )}
        <button
          onClick={toggle}
          className="p-1 rounded hover:bg-surface-hover text-text-secondary cursor-pointer"
        >
          <SodarsIcon name="settings" size={16} />
        </button>
      </div>

      <nav className="flex-1 p-3 space-y-1.5 overflow-y-auto">
        {treeNodes.map(node => renderNode(node))}
      </nav>

      <div className="p-3 border-t border-border dark:border-slate-800 flex flex-col space-y-2">
        <button
          onClick={handleLogout}
          className="text-xs text-left text-danger hover:text-red-400 font-semibold transition-colors cursor-pointer px-3 py-2 rounded hover:bg-surface-hover"
        >
          {isOpen ? 'Sign Out Portal' : 'Exit'}
        </button>
      </div>
    </aside>
  );
};
export default Sidebar;
