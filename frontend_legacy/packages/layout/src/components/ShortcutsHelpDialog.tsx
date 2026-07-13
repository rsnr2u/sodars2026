import React from 'react';
import { useShell } from '../providers/ShellProvider';

export const ShortcutsHelpDialog: React.FC = () => {
  const { shortcutsDialogOpen, setShortcutsDialogOpen } = useShell();

  if (!shortcutsDialogOpen) return null;

  const shortcuts = [
    { keys: ['Ctrl', 'K'], desc: 'Open Command Palette Search Dialog' },
    { keys: ['Alt', '['], desc: 'Collapse Sidebar' },
    { keys: ['Alt', ']'], desc: 'Expand Sidebar' },
    { keys: ['/'], desc: 'Focus Global Search' },
    { keys: ['?'], desc: 'Toggle keyboard shortcuts menu dialog' },
  ];

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 font-sans select-none">
      <div 
        className="fixed inset-0 bg-slate-950/80 backdrop-blur-sm"
        onClick={() => setShortcutsDialogOpen(false)}
      />

      <div className="relative w-full max-w-md overflow-hidden rounded-xl border border-slate-800 bg-slate-900 shadow-2xl p-6 flex flex-col text-slate-300">
        <div className="flex items-center justify-between border-b border-slate-800 pb-3 mb-4">
          <h2 className="text-sm font-bold text-white uppercase tracking-wider">Keyboard Shortcuts</h2>
          <button 
            onClick={() => setShortcutsDialogOpen(false)}
            className="text-slate-500 hover:text-slate-300 text-xs font-semibold cursor-pointer border border-slate-800 px-2 py-0.5 rounded"
          >
            Close
          </button>
        </div>

        <div className="space-y-3">
          {shortcuts.map((s, index) => (
            <div key={index} className="flex items-center justify-between py-1 border-b border-slate-800/30">
              <span className="text-xs font-medium text-slate-400">{s.desc}</span>
              <div className="flex items-center space-x-1">
                {s.keys.map((k, i) => (
                  <React.Fragment key={i}>
                    {i > 0 && <span className="text-[10px] text-slate-600 font-bold">+</span>}
                    <kbd className="px-2 py-0.5 text-[9px] font-bold border border-slate-800 bg-slate-950 text-slate-400 rounded uppercase shadow-sm">
                      {k}
                    </kbd>
                  </React.Fragment>
                ))}
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};
export default ShortcutsHelpDialog;
