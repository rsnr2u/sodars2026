import { Route as TSRoute } from '@tanstack/react-router';
import { Route as protectedRoute } from './_protected';
import { useEnquiries, FollowUp } from '@sodars/module-crm';
import { useState, useEffect } from 'react';
import { SodarsIcon } from '@sodars/icons';

export const Route = new TSRoute({
  getParentRoute: () => protectedRoute,
  path: '/crm/followups',
  component: FollowUpsPageComponent,
});

function FollowUpsPageComponent() {
  const { data: enquiries, isLoading } = useEnquiries();
  const [followups, setFollowups] = useState<FollowUp[]>([]);

  useEffect(() => {
    if (enquiries) {
      const all: FollowUp[] = [];
      enquiries.forEach(e => {
        if (e.followUps) {
          all.push(...e.followUps);
        }
      });
      setFollowups(all);
    }
  }, [enquiries]);

  return (
    <div className="space-y-6 font-sans">
      <div className="flex items-center justify-between border-b border-slate-200 pb-4">
        <div>
          <h2 className="text-2xl font-bold tracking-tight text-slate-900 flex items-center">
            <SodarsIcon name="dashboard" className="text-indigo-600 mr-2.5" size={24} />
            Lead Callbacks & Follow-ups
          </h2>
          <p className="text-slate-500 text-sm">Review scheduled customer call logs, status appointments, and timeline notes.</p>
        </div>
      </div>

      <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
        {isLoading ? (
          <div className="text-center py-12 text-slate-400 text-xs">
            Loading scheduled followups...
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left border-collapse text-xs">
              <thead>
                <tr className="border-b border-slate-200 text-slate-500 font-semibold">
                  <th className="py-3">Follow-up ID</th>
                  <th className="py-3">Enquiry Reference</th>
                  <th className="py-3">Scheduled Date</th>
                  <th className="py-3">Task Details</th>
                  <th className="py-3 text-right">Status</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {followups.map(f => (
                  <tr key={f.id} className="text-slate-700 hover:bg-slate-50 transition-colors">
                    <td className="py-3 font-semibold text-slate-900">{f.id}</td>
                    <td className="py-3 font-mono text-[11px] text-slate-550">{f.enquiryId}</td>
                    <td className="py-3 font-mono text-[11px]">{new Date(f.scheduledAt).toLocaleString()}</td>
                    <td className="py-3 text-slate-500">{f.description}</td>
                    <td className="py-3 text-right">
                      {f.isCompleted ? (
                        <span className="px-2 py-0.5 text-[9px] font-bold bg-slate-100 text-slate-700 rounded-full uppercase">
                          Completed
                        </span>
                      ) : (
                        <span className="px-2 py-0.5 text-[9px] font-bold bg-amber-50 text-amber-700 rounded-full uppercase">
                          Scheduled
                        </span>
                      )}
                    </td>
                  </tr>
                ))}
                {followups.length === 0 && (
                  <tr>
                    <td colSpan={5} className="py-8 text-center text-slate-400 text-xs">
                      No scheduled callback followups found.
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}
export default FollowUpsPageComponent;
