import React from 'react';
import type { Provider } from '../types';

interface ProviderOverviewProps {
  provider: Provider;
}

export const ProviderOverview: React.FC<ProviderOverviewProps> = ({ provider }) => {
  const address = provider.gstRegistration?.registeredAddress;
  const addressStr = address
    ? `${address.street || ''}, ${address.city || ''}, ${address.state || ''}, ${address.zipCode || ''}, ${address.country || ''}`
    : 'No corporate address registered';

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 gap-6 font-sans">
      {/* General Details */}
      <div className="bg-white border border-slate-205 border-slate-200 rounded-xl p-6 shadow-sm space-y-4">
        <h3 className="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">
          Company Identification
        </h3>
        
        <div className="grid grid-cols-3 gap-2 text-xs">
          <div className="text-slate-450 font-medium">Full Legal Name</div>
          <div className="col-span-2 text-slate-800 font-semibold">{provider.name}</div>

          <div className="text-slate-450 font-medium">Business Email</div>
          <div className="col-span-2 text-slate-800 font-mono">{provider.email || 'N/A'}</div>

          <div className="text-slate-450 font-medium">Contact Phone</div>
          <div className="col-span-2 text-slate-800">
            {provider.phone || 'N/A'}
          </div>

          <div className="text-slate-450 font-medium">Registered Address</div>
          <div className="col-span-2 text-slate-800">{addressStr}</div>

          <div className="text-slate-450 font-medium">GST Registered ID</div>
          <div className="col-span-2 text-slate-800 font-mono">
            {provider.gstRegistration?.gstNumber ? (
              <span className="flex items-center space-x-1">
                <span className="px-1.5 py-0.5 bg-emerald-50 text-emerald-700 font-bold rounded text-[10px]">
                  {provider.gstRegistration.gstNumber}
                </span>
                <span className="text-[10px] text-slate-400">
                  (State: {provider.gstRegistration.stateCode})
                </span>
              </span>
            ) : (
              <span className="text-rose-600 font-bold">Unregistered GST</span>
            )}
          </div>
        </div>
      </div>

      {/* Primary Contact Person details */}
      <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-sm space-y-4">
        <h3 className="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">
          Primary Corporate Contact
        </h3>

        {provider.primaryContact ? (
          <div className="grid grid-cols-3 gap-2 text-xs">
            <div className="text-slate-450 font-medium">Full Name</div>
            <div className="col-span-2 text-slate-800 font-semibold">{provider.primaryContact.name}</div>

            <div className="text-slate-450 font-medium">Role Position</div>
            <div className="col-span-2 text-slate-800">{provider.primaryContact.role || 'N/A'}</div>

            <div className="text-slate-450 font-medium">Direct Email</div>
            <div className="col-span-2 text-slate-800 font-mono">{provider.primaryContact.email || 'N/A'}</div>

            <div className="text-slate-450 font-medium">Direct Phone</div>
            <div className="col-span-2 text-slate-800">
              {provider.primaryContact.phone || 'N/A'}
            </div>
          </div>
        ) : (
          <div className="text-slate-400 text-xs py-8 text-center italic">
            No primary contact registry linked.
          </div>
        )}
      </div>

      {/* Bank Coordinates */}
      <div className="bg-white border border-slate-205 border-slate-200 rounded-xl p-6 shadow-sm col-span-1 md:col-span-2 space-y-4">
        <h3 className="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">
          Bank Transaction Coordinates
        </h3>

        {provider.bankAccount ? (
          <div className="grid grid-cols-2 md:grid-cols-4 gap-6 text-xs">
            <div className="space-y-1">
              <div className="text-slate-400 font-medium uppercase tracking-wider text-[10px]">Beneficiary Bank</div>
              <div className="text-slate-800 font-semibold text-sm">{provider.bankAccount.bankName}</div>
            </div>

            <div className="space-y-1">
              <div className="text-slate-400 font-medium uppercase tracking-wider text-[10px]">Holder Name</div>
              <div className="text-slate-800 font-semibold text-sm">{provider.bankAccount.accountHolderName}</div>
            </div>

            <div className="space-y-1">
              <div className="text-slate-400 font-medium uppercase tracking-wider text-[10px]">Account Number</div>
              <div className="text-slate-800 font-mono text-sm font-semibold">{provider.bankAccount.accountNumber}</div>
            </div>

            <div className="space-y-1">
              <div className="text-slate-400 font-medium uppercase tracking-wider text-[10px]">IFSC Code / Routing</div>
              <div className="text-slate-800 font-mono text-sm font-semibold">{provider.bankAccount.routingNumber || 'N/A'}</div>
            </div>
          </div>
        ) : (
          <div className="text-rose-600 text-xs py-4 font-bold">
            ⚠️ No banking deposit coordinates registered. Payments are disabled.
          </div>
        )}
      </div>
    </div>
  );
};
export default ProviderOverview;
