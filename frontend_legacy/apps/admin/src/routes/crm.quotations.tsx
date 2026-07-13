import { Route as TSRoute } from '@tanstack/react-router';
import { Route as protectedRoute } from './_protected';
import { mockCustomers, Quotation } from '@sodars/module-crm';
import { useState } from 'react';
import { SodarsIcon } from '@sodars/icons';

export const Route = new TSRoute({
  getParentRoute: () => protectedRoute,
  path: '/crm/quotations',
  component: QuotationsPageComponent,
});

const mockQuotes: Quotation[] = [
  { id: 'qte_1', customerId: 'cust_1', items: [{ description: 'Hoarding display Metropolis Central (1 month)', quantity: 1, unitPrice: 12000 }], totalAmount: 12000, status: 'Draft', expiresAt: Date.now() + 86400000 * 7 },
  { id: 'qte_2', customerId: 'cust_2', items: [{ description: 'Digital screen Stark Tower (3 months)', quantity: 3, unitPrice: 15000 }], totalAmount: 45000, status: 'Sent', expiresAt: Date.now() + 86400000 * 14 }
];

function QuotationsPageComponent() {
  const [quotes] = useState<Quotation[]>(mockQuotes);

  return (
    <div className="space-y-6 font-sans">
      <div className="flex items-center justify-between border-b border-slate-200 pb-4">
        <div>
          <h2 className="text-2xl font-bold tracking-tight text-slate-900 flex items-center">
            <SodarsIcon name="dashboard" className="text-indigo-600 mr-2.5" size={24} />
            CRM Quotations Proposals
          </h2>
          <p className="text-slate-500 text-sm">Review, edit, and send quotation estimates to active leads and customer companies.</p>
        </div>
      </div>

      <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse text-xs">
            <thead>
              <tr className="border-b border-slate-200 text-slate-500 font-semibold">
                <th className="py-3">Quotation ID</th>
                <th className="py-3">Client Company</th>
                <th className="py-3">Total Amount</th>
                <th className="py-3">Valid Until</th>
                <th className="py-3 text-right">Status</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {quotes.map(q => {
                const client = mockCustomers.find(c => c.id === q.customerId);
                return (
                  <tr key={q.id} className="text-slate-700 hover:bg-slate-50 transition-colors">
                    <td className="py-3 font-semibold text-slate-900">{q.id}</td>
                    <td className="py-3 font-medium">{client ? client.companyName : q.customerId}</td>
                    <td className="py-3 font-extrabold text-indigo-600">${q.totalAmount.toLocaleString()}</td>
                    <td className="py-3 font-mono text-[11px]">{new Date(q.expiresAt).toLocaleDateString()}</td>
                    <td className="py-3 text-right">
                      <span className={`px-2 py-0.5 text-[9px] font-bold rounded-full uppercase ${q.status === 'Sent' ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-700'}`}>
                        {q.status}
                      </span>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
export default QuotationsPageComponent;
