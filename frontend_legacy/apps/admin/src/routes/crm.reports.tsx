import { Route as TSRoute } from '@tanstack/react-router';
import { Route as protectedRoute } from './_protected';
import { SodarsIcon } from '@sodars/icons';

export const Route = new TSRoute({
  getParentRoute: () => protectedRoute,
  path: '/crm/reports',
  component: ReportsPageComponent,
});

function ReportsPageComponent() {
  return (
    <div className="space-y-6 font-sans">
      <div className="flex items-center justify-between border-b border-slate-200 pb-4">
        <div>
          <h2 className="text-2xl font-bold tracking-tight text-slate-900 flex items-center">
            <SodarsIcon name="dashboard" className="text-indigo-600 mr-2.5" size={24} />
            CRM Conversion Reports
          </h2>
          <p className="text-slate-500 text-sm">Visual sales pipelines statistics, client registrations summaries, and conversions ratios.</p>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
        <div className="p-6 bg-white border border-slate-200 rounded-xl shadow-sm space-y-4">
          <h3 className="font-bold text-slate-900 uppercase">Monthly Lead Conversion Rates</h3>
          <div className="h-48 bg-slate-50 border border-slate-100 rounded-lg flex items-end justify-between p-4">
            <div className="w-12 bg-indigo-200 hover:bg-indigo-650 h-[40%] rounded transition-all"></div>
            <div className="w-12 bg-indigo-205 bg-indigo-200 hover:bg-indigo-600 h-[60%] rounded transition-all"></div>
            <div className="w-12 bg-indigo-600 h-[80%] rounded transition-all"></div>
          </div>
          <div className="flex justify-between font-semibold text-slate-500 text-[10px] uppercase">
            <span>May 2026</span>
            <span>Jun 2026</span>
            <span>Jul 2026 (Active)</span>
          </div>
        </div>

        <div className="p-6 bg-white border border-slate-200 rounded-xl shadow-sm space-y-4">
          <h3 className="font-bold text-slate-900 uppercase">Conversion Ratios Summary</h3>
          <div className="space-y-4 py-3">
            <div className="flex justify-between">
              <span>Leads Received</span>
              <span className="font-bold text-slate-900">42 Leads</span>
            </div>
            <div className="flex justify-between border-t border-slate-100 pt-3">
              <span>Leads Won</span>
              <span className="font-bold text-emerald-600">12 Leads (28%)</span>
            </div>
            <div className="flex justify-between border-t border-slate-100 pt-3">
              <span>Leads Lost / Dropped</span>
              <span className="font-bold text-red-600">6 Leads (14%)</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
export default ReportsPageComponent;
