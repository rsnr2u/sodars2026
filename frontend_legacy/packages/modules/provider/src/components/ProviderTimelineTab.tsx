import React from 'react';
import type { Provider } from '../types';

interface ProviderTimelineTabProps {
  provider: Provider;
}

export const ProviderTimelineTab: React.FC<ProviderTimelineTabProps> = ({ provider }) => {
  // Generate chronological events from the provider details
  const events = [
    {
      id: 'e1',
      title: 'Provider Account Created',
      description: 'Account profile initialized in the master registry database.',
      timestamp: provider.createdAt,
      type: 'system',
    },
    ...(provider.gstRegistration
      ? [
          {
            id: 'e2',
            title: 'GST Coordinates Registered',
            description: `GST Number ${provider.gstRegistration.gstNumber} verified (State: ${provider.gstRegistration.stateCode}).`,
            timestamp: provider.gstRegistration.createdAt || provider.createdAt + 1000 * 60 * 65,
            type: 'compliance',
          },
        ]
      : []),
    ...(provider.bankAccount
      ? [
          {
            id: 'e3',
            title: 'Bank Coordinate Linked',
            description: `Transaction deposits routed to ${provider.bankAccount.bankName} (Holder: ${provider.bankAccount.accountHolderName}).`,
            timestamp: provider.bankAccount.createdAt || provider.createdAt + 1000 * 60 * 60 * 2,
            type: 'finance',
          },
        ]
      : []),
    ...provider.documents.map((doc, idx) => ({
      id: `doc-${idx}-${doc.id}`,
      title: `Document safe: ${doc.name} Uploaded`,
      description: `Uploaded document file of type ${doc.type} (${doc.file.mimeType || 'PDF'}).`,
      timestamp: doc.createdAt,
      type: 'compliance',
    })),
    ...provider.agreements.map((agr, idx) => ({
      id: `agr-${idx}-${agr.id}`,
      title: `Agreement executed: ${agr.title}`,
      description: `Signed legally binding service levels and terms.`,
      timestamp: agr.signedAt || agr.createdAt,
      type: 'compliance',
    })),
  ].sort((a, b) => b.timestamp - a.timestamp);

  const getIconBg = (type: string) => {
    switch (type) {
      case 'compliance':
        return 'bg-amber-100 text-amber-600';
      case 'finance':
        return 'bg-emerald-100 text-emerald-600';
      default:
        return 'bg-indigo-100 text-indigo-600';
    }
  };

  return (
    <div className="space-y-6 font-sans">
      <div>
        <h3 className="text-base font-bold text-slate-900">Aggregate Activity Log</h3>
        <p className="text-xs text-slate-500 mt-0.5">Chronological audit log of profile state updates, compliance submissions, and verification steps.</p>
      </div>

      <div className="relative pl-6 border-l border-slate-200 ml-4 space-y-6 py-2">
        {events.map(e => (
          <div key={e.id} className="relative text-xs">
            {/* Timeline bullet dot */}
            <div className={`absolute -left-[31px] top-0.5 w-4 h-4 rounded-full border-2 border-white flex items-center justify-center shadow-sm ${getIconBg(e.type)}`}>
              <span className="w-1.5 h-1.5 rounded-full bg-current"></span>
            </div>

            <div className="bg-white border border-slate-205 border-slate-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-all space-y-1.5 max-w-xl">
              <div className="flex justify-between items-center text-[10px] text-slate-400 font-bold">
                <span className="uppercase tracking-wider">{e.type}</span>
                <span>{new Date(e.timestamp).toLocaleString()}</span>
              </div>
              <h4 className="font-bold text-slate-800 text-xs">{e.title}</h4>
              <p className="text-slate-600 leading-snug">{e.description}</p>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};
export default ProviderTimelineTab;
