import React, { useState } from 'react';
import { useParams } from '@tanstack/react-router';
import { useProvider } from '../hooks/useProvider';
import { useCompliance } from '../hooks/useCompliance';
import {
  ProviderHeader,
  ProviderOverview,
  ProviderBranchesTab,
  ProviderStaffTab,
  ProviderDocumentsTab,
  ProviderAgreementsTab,
  ProviderVerificationTab,
  ProviderTimelineTab,
  ProviderSettingsTab,
} from '../components';

export const ProviderDetailPage: React.FC = () => {
  const params = useParams({ strict: false }) as { id: string };
  const providerId = params.id;

  const { data: provider, isLoading: loadingProvider, updateProvider, deleteProvider } = useProvider(providerId);
  const { status: complianceStatus, isLoading: loadingCompliance } = useCompliance(providerId);
  
  const [activeTab, setActiveTab] = useState<'overview' | 'branches' | 'staff' | 'documents' | 'agreements' | 'verification' | 'timeline' | 'settings'>('overview');

  if (loadingProvider || loadingCompliance || !provider) {
    return <div className="text-slate-400 text-xs py-8 text-center font-sans">Loading corporate provider detail profiler...</div>;
  }

  const handleActivate = async () => {
    try {
      await updateProvider({ isActive: true });
    } catch (err) {
      alert(err instanceof Error ? err.message : String(err));
    }
  };

  const handleSuspend = async () => {
    try {
      await updateProvider({ isActive: false });
    } catch (err) {
      alert(err instanceof Error ? err.message : String(err));
    }
  };

  const handleDelete = async () => {
    try {
      await deleteProvider();
    } catch (err) {
      alert(err instanceof Error ? err.message : String(err));
    }
  };

  const overallStatus = complianceStatus?.overallStatus || 'Pending';

  const renderTabContent = () => {
    switch (activeTab) {
      case 'branches':
        return <ProviderBranchesTab providerId={providerId} />;
      case 'staff':
        return <ProviderStaffTab providerId={providerId} />;
      case 'documents':
        return <ProviderDocumentsTab providerId={providerId} />;
      case 'agreements':
        return <ProviderAgreementsTab providerId={providerId} />;
      case 'verification':
        return <ProviderVerificationTab providerId={providerId} />;
      case 'timeline':
        return <ProviderTimelineTab provider={provider} />;
      case 'settings':
        return <ProviderSettingsTab providerId={providerId} />;
      default:
        return <ProviderOverview provider={provider} />;
    }
  };

  const tabs: { id: typeof activeTab; label: string }[] = [
    { id: 'overview', label: 'Overview' },
    { id: 'branches', label: 'Branches' },
    { id: 'staff', label: 'Staff' },
    { id: 'documents', label: 'Documents safe' },
    { id: 'agreements', label: 'Agreements' },
    { id: 'verification', label: 'Verification' },
    { id: 'timeline', label: 'Timeline Log' },
    { id: 'settings', label: 'Settings' },
  ];

  return (
    <div className="space-y-6 font-sans">
      {/* Header Summary */}
      <ProviderHeader
        provider={provider}
        complianceStatus={overallStatus}
        onActivate={handleActivate}
        onSuspend={handleSuspend}
        onDelete={handleDelete}
      />

      {/* Tabs list */}
      <div className="flex border-b border-slate-200 text-xs font-semibold overflow-x-auto scrollbar-none whitespace-nowrap">
        {tabs.map(t => (
          <button
            key={t.id}
            onClick={() => setActiveTab(t.id)}
            className={`py-3.5 px-5 -mb-[1px] border-b-2 font-bold cursor-pointer transition-all ${
              activeTab === t.id
                ? 'border-indigo-600 text-indigo-650 text-indigo-600'
                : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'
            }`}
          >
            {t.label}
          </button>
        ))}
      </div>

      {/* Tab Panel */}
      <div className="pt-2">
        {renderTabContent()}
      </div>
    </div>
  );
};
export default ProviderDetailPage;
