import { Route as TSRoute, Link } from '@tanstack/react-router';
import { Route as protectedRoute } from './_protected';
import { useCustomers } from '@sodars/module-crm';
import { SodarsIcon } from '@sodars/icons';

export const Route = new TSRoute({
  getParentRoute: () => protectedRoute,
  path: '/crm/customers',
  component: CustomersListComponent,
});

function CustomersListComponent() {
  const { data: customers, isLoading, error } = useCustomers();

  return (
    <div className="space-y-6 font-sans">
      <div className="flex items-center justify-between border-b border-slate-200 pb-4">
        <div>
          <h2 className="text-2xl font-bold tracking-tight text-slate-900 flex items-center">
            <SodarsIcon name="dashboard" className="text-indigo-600 mr-2.5" size={24} />
            Customers Directory
          </h2>
          <p className="text-slate-500 text-sm">Review registered corporate clients, billing profiles, and contact channels.</p>
        </div>
      </div>

      <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
        {isLoading ? (
          <div className="text-center py-12 text-slate-400 text-xs">
            Loading company client profiles...
          </div>
        ) : error ? (
          <div className="text-center py-12 text-red-500 text-xs">
            Error loading customers: {error.message}
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left border-collapse text-xs">
              <thead>
                <tr className="border-b border-slate-200 text-slate-500 font-semibold">
                  <th className="py-3">Company ID</th>
                  <th className="py-3">Company Name</th>
                  <th className="py-3">Primary Email</th>
                  <th className="py-3">Phone Line</th>
                  <th className="py-3 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {customers.map(c => (
                  <tr key={c.id} className="text-slate-700 hover:bg-slate-50 transition-colors">
                    <td className="py-3 font-semibold text-slate-900">{c.id}</td>
                    <td className="py-3 font-medium">{c.companyName}</td>
                    <td className="py-3 font-mono text-[11px]">{c.email}</td>
                    <td className="py-3">{c.phone}</td>
                    <td className="py-3 text-right">
                      <Link
                        to="/crm/customers/$id"
                        params={{ id: c.id }}
                        className="px-3 py-1 bg-slate-900 text-white rounded text-[10px] font-bold hover:bg-slate-800 transition-all cursor-pointer inline-block"
                      >
                        View Profile
                      </Link>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}
export default CustomersListComponent;
