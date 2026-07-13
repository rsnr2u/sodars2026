import { Route as TSRoute } from '@tanstack/react-router';
import { Route as protectedRoute } from './_protected';
import { CustomerService, Customer } from '@sodars/module-crm';
import { useState, useEffect } from 'react';
import { SodarsIcon } from '@sodars/icons';

export const Route = new TSRoute({
  getParentRoute: () => protectedRoute,
  path: '/crm/customers/$id',
  component: CustomerDetailComponent,
});

function CustomerDetailComponent() {
  const { id } = Route.useParams();
  const [customer, setCustomer] = useState<Customer | null>(null);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState<'overview' | 'contacts' | 'timeline'>('overview');

  useEffect(() => {
    CustomerService.getCustomer(id)
      .then(res => {
        setCustomer(res);
        setLoading(false);
      })
      .catch(() => setLoading(false));
  }, [id]);

  if (loading) {
    return <div className="p-8 text-center text-slate-400 text-xs">Loading customer profile details...</div>;
  }

  if (!customer) {
    return <div className="p-8 text-center text-red-500 text-xs">Customer not found.</div>;
  }

  return (
    <div className="space-y-6 font-sans">
      <div className="flex items-center justify-between border-b border-slate-200 pb-4">
        <div>
          <h2 className="text-2xl font-bold tracking-tight text-slate-900 flex items-center">
            <SodarsIcon name="dashboard" className="text-indigo-600 mr-2.5" size={24} />
            {customer.companyName}
          </h2>
          <p className="text-slate-500 text-sm">Customer Profile Account Reference ID: {customer.id}</p>
        </div>
      </div>

      {/* Tabs selectors */}
      <div className="flex space-x-4 border-b border-slate-200 text-xs">
        <button
          onClick={() => setActiveTab('overview')}
          className={`py-2 px-1 font-semibold border-b-2 transition-all cursor-pointer ${activeTab === 'overview' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500'}`}
        >
          Company Overview
        </button>
        <button
          onClick={() => setActiveTab('contacts')}
          className={`py-2 px-1 font-semibold border-b-2 transition-all cursor-pointer ${activeTab === 'contacts' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500'}`}
        >
          Key Contacts ({customer.contacts.length})
        </button>
        <button
          onClick={() => setActiveTab('timeline')}
          className={`py-2 px-1 font-semibold border-b-2 transition-all cursor-pointer ${activeTab === 'timeline' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500'}`}
        >
          Unified Action Timeline ({customer.timeline.length})
        </button>
      </div>

      {/* Tab Contents */}
      <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-sm min-h-[300px]">
        {activeTab === 'overview' && (
          <div className="space-y-6 text-xs text-slate-700">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div className="space-y-3">
                <h4 className="font-bold text-slate-900 uppercase">Primary Coordinates</h4>
                <div>Email: <span className="font-mono text-slate-900 font-semibold">{customer.email}</span></div>
                <div>Phone: <span className="font-semibold text-slate-900">{customer.phone}</span></div>
              </div>
              <div className="space-y-3">
                <h4 className="font-bold text-slate-900 uppercase">Billing Address Coordinates</h4>
                {customer.addresses.map((a, i) => (
                  <div key={i} className="p-3 bg-slate-50 rounded-lg">
                    <div>{a.street}</div>
                    <div>{a.city}, {a.state} {a.zipCode}</div>
                    <div className="font-semibold text-slate-900">{a.country}</div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        )}

        {activeTab === 'contacts' && (
          <div className="space-y-4">
            {customer.contacts.map((c, i) => (
              <div key={i} className="p-4 bg-slate-50 border border-slate-100 rounded-lg flex items-center justify-between text-xs">
                <div>
                  <div className="font-bold text-slate-900">{c.name}</div>
                  <div className="text-[10px] text-slate-500 font-medium uppercase tracking-wider">{c.role || 'Contact'}</div>
                </div>
                <div className="text-right space-y-0.5">
                  <div className="font-mono text-[11px]">{c.email}</div>
                  <div>{c.phone}</div>
                </div>
              </div>
            ))}
          </div>
        )}

        {activeTab === 'timeline' && (
          <div className="relative border-l-2 border-indigo-100 ml-4 pl-6 space-y-6">
            {customer.timeline.map(t => (
              <div key={t.id} className="relative text-xs">
                {/* Dot */}
                <div className="absolute -left-[31px] top-1 w-2.5 h-2.5 rounded-full bg-indigo-600 border border-white"></div>
                <div className="text-[10px] font-bold text-indigo-600 uppercase">{t.type}</div>
                <div className="text-[9px] text-slate-400 font-mono mt-0.5">{new Date(t.timestamp).toLocaleDateString()}</div>
                <p className="text-slate-700 mt-1">{t.details}</p>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
export default CustomerDetailComponent;
