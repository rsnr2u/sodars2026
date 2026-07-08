import { Route as TSRoute } from '@tanstack/react-router';
import { WidgetRegistry } from '@sodars/sdk';
import { Button } from '@sodars/design-system';
import { Route as protectedRoute } from './_protected';

export const Route = new TSRoute({
  getParentRoute: () => protectedRoute,
  path: '/',
  component: DashboardComponent,
});

function DashboardComponent() {
  const widgets = WidgetRegistry.getWidgets();

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-2xl font-bold tracking-tight text-slate-900">Control Room</h2>
          <p className="text-slate-500 text-sm">Real-time modular operations indicators.</p>
        </div>
        <Button variant="primary">Refresh Data</Button>
      </div>

      {widgets.length === 0 ? (
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div className="p-6 bg-white border border-slate-200 rounded-lg shadow-sm">
            <div className="text-sm font-medium text-slate-500">Active Schedules</div>
            <div className="text-2xl font-semibold text-slate-900 mt-2">12 Active</div>
            <div className="text-xs text-emerald-600 mt-1">↑ 8.2% vs yesterday</div>
          </div>
          <div className="p-6 bg-white border border-slate-200 rounded-lg shadow-sm">
            <div className="text-sm font-medium text-slate-500">Resources Allocated</div>
            <div className="text-2xl font-semibold text-slate-900 mt-2">84% Utilized</div>
            <div className="text-xs text-indigo-600 mt-1">14 available drivers</div>
          </div>
          <div className="p-6 bg-white border border-slate-200 rounded-lg shadow-sm">
            <div className="text-sm font-medium text-slate-500">Active Telemetry GPS</div>
            <div className="text-2xl font-semibold text-slate-900 mt-2">4 Online Gateways</div>
            <div className="text-xs text-emerald-600 mt-1">100% active heartbeat</div>
          </div>
          <div className="p-6 bg-white border border-slate-200 rounded-lg shadow-sm">
            <div className="text-sm font-medium text-slate-500">Alerts Raised</div>
            <div className="text-2xl font-semibold text-slate-900 mt-2">0 Conflicts</div>
            <div className="text-xs text-slate-500 mt-1">Scan completed 2m ago</div>
          </div>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-12 gap-6">
          {widgets.map((widget) => {
            const WidgetComp = widget.component;
            return (
              <div
                key={widget.id}
                className="col-span-12 md:col-span-6 p-6 bg-white border border-slate-200 rounded-lg shadow-sm"
              >
                <h3 className="text-sm font-semibold text-slate-500 mb-4">{widget.title}</h3>
                <WidgetComp organizationId="org-999-id" branchId="brh-999-id" />
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
}
