import React, { useState, useEffect, useRef } from 'react';
import { useShell } from '../providers/ShellProvider';
import { CommandRegistry, AppCommand, RegistryManager } from '@sodars/sdk';
import { SodarsIcon } from '@sodars/icons';
import { useIdentity } from '@sodars/auth';

export const CommandPalette: React.FC = () => {
  const { commandPaletteOpen, setCommandPaletteOpen } = useShell();
  const identity = useIdentity();
  const [query, setQuery] = useState('');
  const [selectedIndex, setSelectedIndex] = useState(0);
  const inputRef = useRef<HTMLInputElement>(null);

  const filteredCommands = CommandRegistry.search(query).filter(cmd => {
    // Permission and feature flags gate constraints
    if (cmd.permission && !identity.can(cmd.permission)) return false;
    return true;
  });

  useEffect(() => {
    if (commandPaletteOpen) {
      setQuery('');
      setSelectedIndex(0);
      // Short timeout to let the dialog mount before focusing
      setTimeout(() => inputRef.current?.focus(), 50);
    }
  }, [commandPaletteOpen]);

  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (!commandPaletteOpen) return;

      if (e.key === 'Escape') {
        e.preventDefault();
        setCommandPaletteOpen(false);
      } else if (e.key === 'ArrowDown') {
        e.preventDefault();
        setSelectedIndex((prev) => (prev + 1) % Math.max(1, filteredCommands.length));
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        setSelectedIndex((prev) => (prev - 1 + filteredCommands.length) % Math.max(1, filteredCommands.length));
      } else if (e.key === 'Enter') {
        e.preventDefault();
        if (filteredCommands[selectedIndex]) {
          handleExecute(filteredCommands[selectedIndex]);
        }
      }
    };

    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, [commandPaletteOpen, filteredCommands, selectedIndex, setCommandPaletteOpen]);

  const handleExecute = (cmd: AppCommand) => {
    console.log(`[CommandPalette] Executing command: ${cmd.title}`);
    setCommandPaletteOpen(false);
    
    const mockQueryClient = {
      getQueryData: () => undefined,
      setQueryData: () => {},
    };

    // Inject mock adapters to satisfy context
    const mockContext = {
      router: {
        navigate: async (to: string) => { window.location.href = to; },
        getCurrentPath: () => window.location.pathname,
      },
      identity,
      queryClient: mockQueryClient,
      registry: new RegistryManager(),
    };
    
    cmd.execute(mockContext);
  };

  if (!commandPaletteOpen) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-start justify-center pt-[15vh] px-4 select-none font-sans">
      {/* Backdrop */}
      <div 
        className="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity"
        onClick={() => setCommandPaletteOpen(false)}
      />

      {/* Palette Container */}
      <div className="relative w-full max-w-lg overflow-hidden rounded-xl border border-slate-800 bg-slate-900 shadow-2xl transition-all flex flex-col max-h-[450px]">
        {/* Search Input Box */}
        <div className="flex items-center border-b border-slate-800 px-4 py-3 bg-slate-950">
          <SodarsIcon name="settings" className="text-slate-400 mr-3 animate-spin-slow" size={18} />
          <input
            ref={inputRef}
            type="text"
            placeholder="Type a command or search..."
            value={query}
            onChange={(e) => {
              setQuery(e.target.value);
              setSelectedIndex(0);
            }}
            className="w-full bg-transparent text-slate-100 placeholder-slate-500 text-sm outline-none"
          />
          <span className="text-[10px] text-slate-500 border border-slate-800 px-1.5 py-0.5 rounded uppercase tracking-wider font-semibold">ESC</span>
        </div>

        {/* Results List */}
        <div className="flex-1 overflow-y-auto p-2 space-y-1.5 bg-slate-900">
          {filteredCommands.length === 0 ? (
            <div className="py-8 text-center text-xs text-slate-500 font-medium">
              No matching commands or actions found.
            </div>
          ) : (
            filteredCommands.map((cmd, index) => {
              const isSelected = index === selectedIndex;
              return (
                <div
                  key={cmd.id}
                  onClick={() => handleExecute(cmd)}
                  className={`flex items-center justify-between px-3 py-2.5 rounded-lg cursor-pointer transition-all ${
                    isSelected 
                      ? 'bg-indigo-600 text-white shadow-md' 
                      : 'hover:bg-slate-800/50 text-slate-300'
                  }`}
                >
                  <div className="flex items-center space-x-3">
                    {cmd.icon ? (
                      <SodarsIcon name={cmd.icon} size={16} />
                    ) : (
                      <SodarsIcon name="settings" size={16} />
                    )}
                    <div className="flex flex-col">
                      <span className="text-xs font-semibold">{cmd.title}</span>
                      {cmd.description && (
                        <span className={`text-[10px] ${isSelected ? 'text-indigo-200' : 'text-slate-500'}`}>
                          {cmd.description}
                        </span>
                      )}
                    </div>
                  </div>

                  <div className="flex items-center space-x-2">
                    {cmd.shortcut && (
                      <span className={`text-[9px] font-bold border px-1.5 py-0.5 rounded uppercase tracking-wider ${
                        isSelected 
                          ? 'border-indigo-500 bg-indigo-700 text-white' 
                          : 'border-slate-800 bg-slate-950 text-slate-500'
                      }`}>
                        {cmd.shortcut}
                      </span>
                    )}
                    {cmd.group && (
                      <span className={`text-[9px] uppercase font-bold tracking-wider px-2 py-0.5 rounded ${
                        isSelected ? 'bg-indigo-700 text-indigo-100' : 'bg-slate-800 text-slate-400'
                      }`}>
                        {cmd.group}
                      </span>
                    )}
                  </div>
                </div>
              );
            })
          )}
        </div>

        {/* Footer help guide bar */}
        <div className="border-t border-slate-800 px-4 py-2 flex items-center justify-between text-[10px] text-slate-500 bg-slate-950">
          <div className="flex items-center space-x-3">
            <span>↑↓ Navigation</span>
            <span>↵ Select</span>
          </div>
          <span>Total: {filteredCommands.length} commands</span>
        </div>
      </div>
    </div>
  );
};
export default CommandPalette;
