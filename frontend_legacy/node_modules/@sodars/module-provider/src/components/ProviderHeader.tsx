import React from 'react';
import type { Provider } from '../types';
import { ProviderStatus } from '../enums';
import { SodarsIcon } from '@sodars/icons';

interface ProviderHeaderProps {
  provider: Provider;
  complianceStatus: 'Compliant' | 'Pending' | 'Expired';
  onActivate: () => Promise<void>;
  onSuspend: () => Promise<void>;
  onDelete: () => Promise<void>;
}

export const ProviderHeader: React.FC<ProviderHeaderProps> = ({
  provider,
  complianceStatus,
  onActivate,
  onSuspend,
  onDelete,
}) => {
  const isSuspended = !provider.isActive;

  const getStatusBadgeClass = (status: ProviderStatus) => {
    switch (status) {
      case ProviderStatus.Verified:
        return 'bg-emerald-100 text-emerald-800 border-emerald-200';
      case ProviderStatus.Rejected:
        return 'bg-rose-100 text-rose-800 border-rose-200';
      case ProviderStatus.UnderReview:
        return 'bg-amber-100 text-amber-800 border-amber-200';
      default:
        return 'bg-slate-100 text-slate-800 border-slate-200';
    }
  };

  const getComplianceBadgeClass = (status: 'Compliant' | 'Pending' | 'Expired') => {
    switch (status) {
      case 'Compliant':
        return 'bg-emerald-100 text-emerald-800 border-emerald-200';
      case 'Expired':
        return 'bg-rose-100 text-rose-800 border-rose-200';
      default:
        return 'bg-amber-100 text-amber-850 border-amber-200';
    }
  };

  return (
    <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-sm font-sans flex flex-col md:flex-row md:items-center md:justify-between gap-6">
      <div className="flex items-center space-x-4">
        <div className="w-16 h-16 rounded-xl bg-indigo-50 border border-indigo-105 border-indigo-100 flex items-center justify-center text-indigo-600 shadow-inner">
          <SodarsIcon name="provider" size={32} />
        </div>
        <div>
          <div className="flex items-center space-x-2">
            <h1 className="text-xl font-extrabold text-slate-900 tracking-tight">{provider.name}</h1>
            <span className={`px-2.5 py-0.5 rounded-full text-xs font-bold border ${getStatusBadgeClass(provider.status)}`}>
              {provider.status}
            </span>
            <span className={`px-2.5 py-0.5 rounded-full text-xs font-bold border ${getComplianceBadgeClass(complianceStatus)}`}>
              {complianceStatus}
            </span>
          </div>
          <p className="text-xs text-slate-500 mt-1 flex items-center">
            <span className="font-mono text-slate-400 mr-2">ID: {provider.id}</span>
            <span className="w-1 h-1 bg-slate-300 rounded-full mx-2"></span>
            <span>Created {new Date(provider.createdAt).toLocaleDateString()}</span>
          </p>
        </div>
      </div>

      <div className="flex flex-wrap gap-3">
        {isSuspended ? (
          <button
            onClick={onActivate}
            className="px-4 py-2 bg-emerald-650 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm flex items-center space-x-1.5 cursor-pointer"
          >
            <span className="w-2 h-2 bg-white rounded-full animate-ping"></span>
            <span>Activate Provider</span>
          </button>
        ) : (
          <button
            onClick={onSuspend}
            className="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm flex items-center space-x-1.5 cursor-pointer"
          >
            <span>Suspend Profile</span>
          </button>
        )}

        <button
          onClick={onDelete}
          className="px-4 py-2 bg-white hover:bg-rose-50 border border-slate-200 hover:border-rose-250 text-slate-700 hover:text-rose-700 rounded-lg text-xs font-bold transition-all cursor-pointer"
        >
          Delete Account
        </button>
      </div>
    </div>
  );
};
export default ProviderHeader;
