import { Route as TSRoute } from '@tanstack/react-router';
import { Route as protectedRoute } from './_protected';
import { mockRoles } from '@sodars/module-iam';
import { SodarsIcon } from '@sodars/icons';

export const Route = new TSRoute({
  getParentRoute: () => protectedRoute,
  path: '/iam/roles',
  component: RolesComponent,
});

function RolesComponent() {
  return (
    <div className="space-y-6 font-sans">
      <div className="flex items-center justify-between border-b border-slate-200 pb-4">
        <div>
          <h2 className="text-2xl font-bold tracking-tight text-slate-900 flex items-center">
            <SodarsIcon name="settings" className="text-indigo-600 mr-2.5" size={24} />
            Roles & Access Policies
          </h2>
          <p className="text-slate-500 text-sm">Configure security groups permissions and resource boundaries.</p>
        </div>
      </div>

      <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse text-xs">
            <thead>
              <tr className="border-b border-slate-200 text-slate-500 font-semibold">
                <th className="py-3">Role ID</th>
                <th className="py-3">Display Name</th>
                <th className="py-3">Description</th>
                <th className="py-3">Permissions Matrix</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {mockRoles.map(r => (
                <tr key={r.id} className="text-slate-700 hover:bg-slate-50 transition-colors">
                  <td className="py-3 font-semibold text-slate-900">{r.id}</td>
                  <td className="py-3">{r.name}</td>
                  <td className="py-3 text-slate-500">{r.description}</td>
                  <td className="py-3">
                    <div className="flex flex-wrap gap-1">
                      {r.permissions.map(p => (
                        <span key={p} className="px-1.5 py-0.5 bg-indigo-50 text-indigo-700 rounded font-bold text-[8px]">
                          {p}
                        </span>
                      ))}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
export default RolesComponent;
