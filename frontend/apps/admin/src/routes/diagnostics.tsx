import { useState, useEffect } from 'react';
import { Route as TSRoute } from '@tanstack/react-router';
import { Route as protectedRoute } from './_protected';
import { 
  NavigationRegistry, 
  WidgetRegistry, 
  CommandRegistry, 
  ModuleManager 
} from '@sodars/sdk';
import { EventBus } from '@sodars/events';
import { FeatureFlagStore, environmentConfig } from '@sodars/config';
import { useIdentity } from '@sodars/auth';
import { SodarsIcon } from '@sodars/icons';

export const Route = new TSRoute({
  getParentRoute: () => protectedRoute,
  path: '/diagnostics',
  component: DiagnosticsComponent,
});

type TabType =
  | 'overview'
  | 'modules'
  | 'registries'
  | 'eventbus'
  | 'flags'
  | 'environment';

interface EventLog {
  id: string;
  timestamp: number;
  event: string;
  category: string;
  severity: string;
  duration?: number;
  correlationId?: string;
  properties?: any;
}

function DiagnosticsComponent() {
  const identity = useIdentity();
  const [activeTab, setActiveTab] = useState<TabType>('overview');
  const [eventsList, setEventsList] = useState<EventLog[]>([]);
  const [searchQuery, setSearchQuery] = useState('');
  const [severityFilter, setSeverityFilter] = useState('ALL');

  // Enforce security boundaries
  const isDev = environmentConfig.isDev;
  const hasPermission = identity.can('system.diagnostics');

  useEffect(() => {
    // Listen to live telemetry events from EventBus
    const unsubscribe = EventBus.subscribe('telemetry:event', (payload: any) => {
      const newEvent: EventLog = {
        id: Math.random().toString(36).substring(2, 9),
        timestamp: payload.timestamp || Date.now(),
        event: payload.event || 'generic:event',
        category: payload.event?.split(':')[0] || 'generic',
        severity: payload.severity !== undefined ? String(payload.severity) : 'INFO',
        duration: payload.duration,
        correlationId: payload.correlationId,
        properties: payload.properties,
      };
      setEventsList((prev) => [newEvent, ...prev].slice(0, 100)); // Cap logs at 100 entries
    });

    const errorUnsubscribe = EventBus.subscribe('telemetry:error', (payload: any) => {
      const newError: EventLog = {
        id: Math.random().toString(36).substring(2, 9),
        timestamp: payload.timestamp || Date.now(),
        event: `error:${payload.errorName || 'exception'}`,
        category: 'error',
        severity: 'ERROR',
        correlationId: payload.correlationId,
        properties: { message: payload.message, stack: payload.stack },
      };
      setEventsList((prev) => [newError, ...prev].slice(0, 100));
    });

    return () => {
      unsubscribe();
      errorUnsubscribe();
    };
  }, []);

  if (!isDev && !hasPermission) {
    return (
      <div className="flex items-center justify-center min-h-[50vh] text-center p-6">
        <div className="space-y-3">
          <SodarsIcon name="settings" className="text-red-500 mx-auto animate-pulse" size={48} />
          <h2 className="text-xl font-bold text-slate-900 dark:text-white">Access Denied</h2>
          <p className="text-slate-500 text-sm max-w-md">
            The developer diagnostics console is restricted to local development environments or accounts holding authorized system permissions.
          </p>
        </div>
      </div>
    );
  }

  // Get active statistics
  const navStats = NavigationRegistry.getStats();
  const widgetStats = WidgetRegistry.getStats();
  const commandStats = CommandRegistry.getStats();
  const modulesList = ModuleManager.getModules();

  const filteredEvents = eventsList.filter((ev) => {
    if (severityFilter !== 'ALL' && ev.severity !== severityFilter) return false;
    if (searchQuery) {
      const query = searchQuery.toLowerCase();
      return (
        ev.event.toLowerCase().includes(query) ||
        ev.category.toLowerCase().includes(query) ||
        (ev.correlationId && ev.correlationId.toLowerCase().includes(query))
      );
    }
    return true;
  });

  return (
    <div className="space-y-6 font-sans">
      {/* Header bar */}
      <div className="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
        <div>
          <h2 className="text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center">
            <SodarsIcon name="settings" className="text-indigo-600 mr-2.5 animate-spin-slow" size={24} />
            Diagnostics Console
          </h2>
          <p className="text-slate-500 text-sm">Platform status controls, registries stats, and realtime telemetry event streams.</p>
        </div>
        <div className="flex items-center space-x-2">
          <span className="px-2.5 py-1 rounded bg-indigo-50 text-indigo-700 text-xs font-bold border border-indigo-100">
            ENV: {environmentConfig.mode.toUpperCase()}
          </span>
          <button 
            onClick={() => setEventsList([])}
            className="px-3 py-1 bg-slate-900 text-white rounded text-xs font-bold hover:bg-slate-800 transition-all border border-slate-850 cursor-pointer"
          >
            Clear Event Logs
          </button>
        </div>
      </div>

      {/* Navigation tabs */}
      <div className="flex border-b border-slate-200 dark:border-slate-800 space-x-4">
        {(['overview', 'modules', 'registries', 'eventbus', 'flags', 'environment'] as TabType[]).map((tab) => (
          <button
            key={tab}
            onClick={() => setActiveTab(tab)}
            className={`pb-2.5 text-sm font-bold uppercase tracking-wider border-b-2 transition-all cursor-pointer ${
              activeTab === tab 
                ? 'border-indigo-600 text-indigo-600' 
                : 'border-transparent text-slate-500 hover:text-slate-950'
            }`}
          >
            {tab}
          </button>
        ))}
      </div>

      {/* Contents based on tab */}
      <div className="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 shadow-sm min-h-[300px]">
        
        {/* OVERVIEW TAB */}
        {activeTab === 'overview' && (
          <div className="space-y-6">
            <h3 className="text-md font-bold text-slate-900 dark:text-white uppercase tracking-wide">Kernel Status Summary</h3>
            <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
              <div className="p-4 border border-slate-200 dark:border-slate-800 rounded-lg">
                <span className="text-xs text-slate-500 font-semibold block">Registered Modules</span>
                <span className="text-2xl font-bold text-slate-900 dark:text-white mt-1 block">{modulesList.length}</span>
              </div>
              <div className="p-4 border border-slate-200 dark:border-slate-800 rounded-lg">
                <span className="text-xs text-slate-500 font-semibold block">Navigation Nodes</span>
                <span className="text-2xl font-bold text-slate-900 dark:text-white mt-1 block">{navStats.total}</span>
              </div>
              <div className="p-4 border border-slate-200 dark:border-slate-800 rounded-lg">
                <span className="text-xs text-slate-500 font-semibold block">Registered Widgets</span>
                <span className="text-2xl font-bold text-slate-900 dark:text-white mt-1 block">{widgetStats.total}</span>
              </div>
              <div className="p-4 border border-slate-200 dark:border-slate-800 rounded-lg">
                <span className="text-xs text-slate-500 font-semibold block">Registered Commands</span>
                <span className="text-2xl font-bold text-slate-900 dark:text-white mt-1 block">{commandStats.total}</span>
              </div>
            </div>

            <div className="p-4 bg-slate-50 dark:bg-slate-950 rounded-lg border border-slate-200 dark:border-slate-850">
              <h4 className="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-2">Authenticated Context</h4>
              <pre className="text-[11px] text-slate-600 dark:text-slate-400 overflow-x-auto">
                {JSON.stringify({
                  isAuthenticated: identity.isAuthenticated(),
                  user: identity.user(),
                  organization: identity.organization(),
                  branch: identity.branch(),
                  roles: identity.roles(),
                  permissions: identity.permissions()
                }, null, 2)}
              </pre>
            </div>
          </div>
        )}

        {/* MODULES TAB */}
        {activeTab === 'modules' && (
          <div className="space-y-4">
            <h3 className="text-md font-bold text-slate-900 dark:text-white uppercase tracking-wide">Loaded Modules & Lifecycle Manifest</h3>
            <div className="overflow-x-auto">
              <table className="w-full text-left border-collapse text-xs">
                <thead>
                  <tr className="border-b border-slate-250 dark:border-slate-850 text-slate-500 font-semibold">
                    <th className="py-2.5">Module ID</th>
                    <th className="py-2.5">Display Name</th>
                    <th className="py-2.5">Version</th>
                    <th className="py-2.5">Category</th>
                    <th className="py-2.5">Dependencies</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                  {modulesList.map(mod => (
                    <tr key={mod.id} className="text-slate-700 dark:text-slate-350">
                      <td className="py-3 font-semibold text-slate-900 dark:text-white">{mod.id}</td>
                      <td className="py-3">{mod.displayName}</td>
                      <td className="py-3">{mod.version}</td>
                      <td className="py-3">
                        <span className="px-2 py-0.5 bg-slate-100 rounded text-slate-600 uppercase font-bold text-[9px]">
                          {mod.category || 'business'}
                        </span>
                      </td>
                      <td className="py-3">{mod.dependencies?.join(', ') || 'None'}</td>
                    </tr>
                  ))}
                  {modulesList.length === 0 && (
                    <tr>
                      <td colSpan={5} className="py-6 text-center text-slate-500">No pluggable business modules bootstrapped.</td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {/* REGISTRIES TAB */}
        {activeTab === 'registries' && (
          <div className="space-y-6">
            <h3 className="text-md font-bold text-slate-900 dark:text-white uppercase tracking-wide">SDK Registries Audit Metrics</h3>
            <div className="space-y-4">
              <div className="p-4 border border-slate-250 dark:border-slate-850 rounded-lg">
                <h4 className="text-xs font-bold text-indigo-600 uppercase tracking-wider mb-2">NavigationRegistry (v{navStats.version})</h4>
                <div className="text-xs text-slate-500 space-y-1">
                  <div>Last Updated: {new Date(navStats.lastUpdated).toLocaleTimeString()}</div>
                  <div>Registered Module Targets: {navStats.modules} modules</div>
                </div>
              </div>

              <div className="p-4 border border-slate-250 dark:border-slate-850 rounded-lg">
                <h4 className="text-xs font-bold text-indigo-600 uppercase tracking-wider mb-2">WidgetRegistry (v{widgetStats.version})</h4>
                <div className="text-xs text-slate-500 space-y-1">
                  <div>Last Updated: {new Date(widgetStats.lastUpdated).toLocaleTimeString()}</div>
                  <div>Active Dashboard Widgets: {widgetStats.total} items</div>
                </div>
              </div>

              <div className="p-4 border border-slate-250 dark:border-slate-850 rounded-lg">
                <h4 className="text-xs font-bold text-indigo-600 uppercase tracking-wider mb-2">CommandRegistry (v{commandStats.version})</h4>
                <div className="text-xs text-slate-500 space-y-1">
                  <div>Last Updated: {new Date(commandStats.lastUpdated).toLocaleTimeString()}</div>
                  <div>Fuzzy Search Commands: {commandStats.total} entries</div>
                </div>
              </div>
            </div>
          </div>
        )}

        {/* EVENT BUS LOGS TAB */}
        {activeTab === 'eventbus' && (
          <div className="space-y-4">
            <div className="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 mb-4">
              <h3 className="text-md font-bold text-slate-900 dark:text-white uppercase tracking-wide">Live Event Stream Log</h3>
              <div className="flex items-center space-x-3">
                <select 
                  value={severityFilter}
                  onChange={(e) => setSeverityFilter(e.target.value)}
                  className="bg-slate-50 border border-slate-250 text-slate-700 text-xs rounded px-2.5 py-1 outline-none"
                >
                  <option value="ALL">Severity: ALL</option>
                  <option value="INFO">INFO</option>
                  <option value="ERROR">ERROR</option>
                  <option value="WARNING">WARNING</option>
                </select>
                <input
                  type="text"
                  placeholder="Filter logs..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="bg-slate-50 border border-slate-250 text-slate-700 text-xs rounded px-3 py-1 outline-none w-48"
                />
              </div>
            </div>

            <div className="space-y-2 max-h-[400px] overflow-y-auto pr-2">
              {filteredEvents.length === 0 ? (
                <div className="text-center py-12 text-slate-400 text-xs">
                  Waiting for events... Trigger API calls, command palette searches, or navigate screens.
                </div>
              ) : (
                filteredEvents.map((ev) => (
                  <div key={ev.id} className="p-3 border border-slate-100 dark:border-slate-850 rounded bg-slate-50 dark:bg-slate-950 font-mono text-[10px] space-y-1.5 shadow-xs">
                    <div className="flex items-center justify-between">
                      <div className="flex items-center space-x-2">
                        <span className="text-slate-500 font-semibold">{new Date(ev.timestamp).toLocaleTimeString()}</span>
                        <span className="px-1.5 py-0.5 bg-indigo-50 text-indigo-700 rounded font-bold">{ev.event}</span>
                        <span className="px-2 py-0.5 bg-slate-200 rounded uppercase font-bold text-[8px]">{ev.category}</span>
                      </div>
                      <div className="flex items-center space-x-2">
                        {ev.duration !== undefined && (
                          <span className="text-emerald-600 font-semibold">{ev.duration}ms</span>
                        )}
                        <span className={`px-1.5 py-0.5 rounded text-[8px] font-bold ${
                          ev.severity === 'ERROR' ? 'bg-red-50 text-red-700' : 'bg-slate-100 text-slate-600'
                        }`}>
                          {ev.severity}
                        </span>
                      </div>
                    </div>
                    {ev.correlationId && (
                      <div className="text-slate-400">CorrID: {ev.correlationId}</div>
                    )}
                    {ev.properties && (
                      <pre className="p-2 bg-slate-100 dark:bg-slate-900 rounded overflow-x-auto text-slate-650 max-h-[100px]">
                        {JSON.stringify(ev.properties, null, 2)}
                      </pre>
                    )}
                  </div>
                ))
              )}
            </div>
          </div>
        )}

        {/* FEATURE FLAGS TAB */}
        {activeTab === 'flags' && (
          <div className="space-y-4">
            <h3 className="text-md font-bold text-slate-900 dark:text-white uppercase tracking-wide">Feature Flags Registry Gating</h3>
            <div className="space-y-2">
              {[
                { name: 'enableCommandPalette', desc: 'Activates command search popup Ctrl+K.' },
                { name: 'enableRealtimeNotifications', desc: 'Mounts event telemetry push listeners.' },
                { name: 'enableWalletLedger', desc: 'Beta finance ledger interface screens.' }
              ].map((flag) => {
                const evalVal = FeatureFlagStore.evaluate(flag.name as any, {});
                return (
                  <div key={flag.name} className="flex items-center justify-between p-3 border border-slate-200 dark:border-slate-800 rounded-lg">
                    <div>
                      <span className="text-xs font-bold text-slate-900 dark:text-white block">{flag.name}</span>
                      <span className="text-[10px] text-slate-500 block mt-0.5">{flag.desc}</span>
                    </div>
                    <span className={`px-3 py-1 rounded text-xs font-bold ${
                      evalVal ? 'bg-emerald-55 text-emerald-700' : 'bg-red-50 text-red-700'
                    }`}>
                      {evalVal ? 'ACTIVE / ON' : 'DISABLED / OFF'}
                    </span>
                  </div>
                );
              })}
            </div>
          </div>
        )}

        {/* ENVIRONMENT TAB */}
        {activeTab === 'environment' && (
          <div className="space-y-4">
            <h3 className="text-md font-bold text-slate-900 dark:text-white uppercase tracking-wide">Environment Variables Context</h3>
            <div className="p-4 bg-slate-50 dark:bg-slate-950 rounded-lg border border-slate-250 dark:border-slate-850">
              <pre className="text-xs text-slate-500 font-mono overflow-x-auto">
                {JSON.stringify({
                  mode: environmentConfig.mode,
                  isDev: environmentConfig.isDev,
                  isProduction: environmentConfig.isProduction,
                  navigator: {
                    userAgent: navigator.userAgent,
                    language: navigator.language,
                    onLine: navigator.onLine,
                  }
                }, null, 2)}
              </pre>
            </div>
          </div>
        )}

      </div>
    </div>
  );
}
export default DiagnosticsComponent;
