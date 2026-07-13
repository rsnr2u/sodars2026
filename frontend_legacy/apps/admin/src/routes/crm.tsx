import { Route as TSRoute } from '@tanstack/react-router';
import { Route as protectedRoute } from './_protected';
import { useEnquiries, useCustomers, TaskService, Task } from '@sodars/module-crm';
import { useState, useEffect } from 'react';
import { SodarsIcon } from '@sodars/icons';

export const Route = new TSRoute({
  getParentRoute: () => protectedRoute,
  path: '/crm',
  component: CrmDashboardComponent,
});

function CrmDashboardComponent() {
  const { data: enquiries, isLoading: loadingEnq } = useEnquiries();
  const { data: customers, isLoading: loadingCust } = useCustomers();
  const [tasks, setTasks] = useState<Task[]>([]);
  const [loadingTasks, setLoadingTasks] = useState(true);

  useEffect(() => {
    TaskService.getTasks()
      .then(res => {
        setTasks(res);
        setLoadingTasks(false);
      })
      .catch(() => setLoadingTasks(false));
  }, []);

  const totalPipelineValue = enquiries.reduce((acc, curr) => acc + curr.value, 0);
  const openLeadsCount = enquiries.filter(e => e.stage !== 'Won' && e.stage !== 'Lost').length;
  const convertedCount = enquiries.filter(e => e.stage === 'Won').length;

  return (
    <div className="space-y-6 font-sans">
      <div className="flex items-center justify-between border-b border-slate-200 pb-4">
        <div>
          <h2 className="text-2xl font-bold tracking-tight text-slate-900 flex items-center">
            <SodarsIcon name="dashboard" className="text-indigo-600 mr-2.5" size={24} />
            CRM Control Dashboard
          </h2>
          <p className="text-slate-500 text-sm">Real-time overview of active enquiry pipelines, conversion rates, and sales tasks.</p>
        </div>
      </div>

      {/* Stats Grids */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div className="p-6 bg-white border border-slate-200 rounded-xl shadow-sm hover:shadow-md transition-shadow">
          <div className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Active Enquiry Leads</div>
          <div className="text-3xl font-extrabold text-slate-900 mt-2">{loadingEnq ? '...' : openLeadsCount}</div>
          <div className="text-[10px] text-emerald-600 font-bold mt-1.5 flex items-center">
            <span className="w-1.5 h-1.5 bg-emerald-500 rounded-full inline-block mr-1"></span>
            Leads pipeline active
          </div>
        </div>

        <div className="p-6 bg-white border border-slate-200 rounded-xl shadow-sm hover:shadow-md transition-shadow">
          <div className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Pipeline Value</div>
          <div className="text-3xl font-extrabold text-indigo-600 mt-2">
            ${loadingEnq ? '...' : totalPipelineValue.toLocaleString()}
          </div>
          <div className="text-[10px] text-indigo-600 font-bold mt-1.5">
            Active opportunities value
          </div>
        </div>

        <div className="p-6 bg-white border border-slate-200 rounded-xl shadow-sm hover:shadow-md transition-shadow">
          <div className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Conversions Count</div>
          <div className="text-3xl font-extrabold text-emerald-600 mt-2">
            {loadingEnq ? '...' : convertedCount}
          </div>
          <div className="text-[10px] text-slate-500 mt-1.5">
            Converted accounts this cycle
          </div>
        </div>

        <div className="p-6 bg-white border border-slate-200 rounded-xl shadow-sm hover:shadow-md transition-shadow">
          <div className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Active Customers</div>
          <div className="text-3xl font-extrabold text-slate-900 mt-2">
            {loadingCust ? '...' : customers.length}
          </div>
          <div className="text-[10px] text-slate-500 mt-1.5">
            Registered corporate clients
          </div>
        </div>
      </div>

      {/* Split layout: Tasks & Lead Sources */}
      <div className="grid grid-cols-1 md:grid-cols-12 gap-6">
        {/* Task lists checklist */}
        <div className="col-span-12 md:col-span-8 p-6 bg-white border border-slate-200 rounded-xl shadow-sm">
          <h3 className="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">
            CRM Action Tasks List
          </h3>
          {loadingTasks ? (
            <div className="text-slate-400 text-xs py-4 text-center">Loading task board list...</div>
          ) : (
            <div className="space-y-3">
              {tasks.map(t => (
                <div key={t.id} className="flex items-center justify-between p-3 bg-slate-50 hover:bg-slate-100 rounded-lg transition-colors text-xs">
                  <div className="flex items-center space-x-3">
                    <span className={`w-2 h-2 rounded-full ${t.priority === 'High' ? 'bg-red-500' : t.priority === 'Medium' ? 'bg-amber-500' : 'bg-blue-500'}`}></span>
                    <span className={`text-slate-800 ${t.isCompleted ? 'line-through text-slate-400' : ''}`}>{t.title}</span>
                  </div>
                  <span className={`px-2 py-0.5 rounded font-bold text-[9px] uppercase ${t.priority === 'High' ? 'bg-red-100 text-red-700' : t.priority === 'Medium' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700'}`}>
                    {t.priority}
                  </span>
                </div>
              ))}
            </div>
          )}
        </div>

        {/* Lead sources summary metrics */}
        <div className="col-span-12 md:col-span-4 p-6 bg-white border border-slate-200 rounded-xl shadow-sm">
          <h3 className="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">
            Inbound Channels
          </h3>
          <div className="space-y-4">
            <div className="space-y-1.5">
              <div className="flex justify-between text-xs text-slate-650">
                <span>Organic Website</span>
                <span className="font-semibold">60%</span>
              </div>
              <div className="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                <div className="bg-indigo-600 h-full rounded-full" style={{ width: '60%' }}></div>
              </div>
            </div>
            <div className="space-y-1.5">
              <div className="flex justify-between text-xs text-slate-650">
                <span>Partner Referral</span>
                <span className="font-semibold">30%</span>
              </div>
              <div className="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                <div className="bg-emerald-505 bg-emerald-500 h-full rounded-full" style={{ width: '30%' }}></div>
              </div>
            </div>
            <div className="space-y-1.5">
              <div className="flex justify-between text-xs text-slate-650">
                <span>Paid Campaigns</span>
                <span className="font-semibold">10%</span>
              </div>
              <div className="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                <div className="bg-amber-500 h-full rounded-full" style={{ width: '10%' }}></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
export default CrmDashboardComponent;
