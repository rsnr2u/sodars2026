import React, { useState } from 'react';
import { useCompliance } from '../hooks/useCompliance';
import { useProvider } from '../hooks/useProvider';

interface ProviderSettingsTabProps {
  providerId: string;
}

export const ProviderSettingsTab: React.FC<ProviderSettingsTabProps> = ({ providerId }) => {
  const { data: provider, deleteProvider } = useProvider(providerId);
  const { updateBankAccount, updateGSTRegistration } = useCompliance(providerId);

  // Bank Form State
  const [bankName, setBankName] = useState(provider?.bankAccount?.bankName || '');
  const [accountHolderName, setAccountHolderName] = useState(provider?.bankAccount?.accountHolderName || '');
  const [accountNumber, setAccountNumber] = useState(provider?.bankAccount?.accountNumber || '');
  const [routingNumber, setRoutingNumber] = useState(provider?.bankAccount?.routingNumber || '');

  // GST Form State
  const [gstNumber, setGstNumber] = useState(provider?.gstRegistration?.gstNumber || '');
  const [stateCode, setStateCode] = useState(provider?.gstRegistration?.stateCode || '');

  const handleSaveBank = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!bankName || !accountNumber) return;
    try {
      await updateBankAccount({
        id: provider?.bankAccount?.id || `bank-${Date.now()}`,
        providerId,
        bankName,
        accountHolderName,
        accountNumber,
        routingNumber,
        createdAt: provider?.bankAccount?.createdAt || Date.now(),
        updatedAt: Date.now(),
        version: (provider?.bankAccount?.version || 0) + 1,
        isActive: true,
      });
      alert('Bank deposit coordinates updated successfully.');
    } catch (err) {
      alert(err instanceof Error ? err.message : String(err));
    }
  };

  const handleSaveGST = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!gstNumber) return;
    try {
      await updateGSTRegistration({
        id: provider?.gstRegistration?.id || `gst-${Date.now()}`,
        providerId,
        gstNumber,
        stateCode,
        registeredAddress: provider?.gstRegistration?.registeredAddress || {
          street: '',
          city: '',
          state: '',
          zipCode: '',
          country: '',
          isBilling: false,
        },
        createdAt: provider?.gstRegistration?.createdAt || Date.now(),
        updatedAt: Date.now(),
        version: (provider?.gstRegistration?.version || 0) + 1,
        isActive: true,
      });
      alert('GST corporate identifier registered successfully.');
    } catch (err) {
      alert(err instanceof Error ? err.message : String(err));
    }
  };

  const handleDelete = async () => {
    if (!confirm('Are you absolutely sure you want to permanently delete this provider profile?')) return;
    try {
      await deleteProvider();
      window.location.hash = '#/providers'; // Redirect
    } catch (err) {
      alert(err instanceof Error ? err.message : String(err));
    }
  };

  if (!provider) return null;

  return (
    <div className="space-y-8 font-sans text-xs">
      {/* GST Settings */}
      <div className="bg-white border border-slate-205 border-slate-200 rounded-xl p-6 shadow-sm space-y-4">
        <h3 className="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">
          Corporate GST Identification Settings
        </h3>

        <form onSubmit={handleSaveGST} className="space-y-4">
          <div className="grid grid-cols-2 gap-4 max-w-xl">
            <div className="space-y-1">
              <label className="font-semibold text-slate-600">GST Registration Number</label>
              <input
                value={gstNumber}
                onChange={e => setGstNumber(e.target.value)}
                placeholder="e.g. GST-9988776655"
                className="w-full border border-slate-200 rounded p-2 outline-none focus:border-indigo-500 font-mono text-xs"
                required
              />
            </div>
            <div className="space-y-1">
              <label className="font-semibold text-slate-600">State Code</label>
              <input
                value={stateCode}
                onChange={e => setStateCode(e.target.value)}
                placeholder="e.g. NSW"
                className="w-full border border-slate-200 rounded p-2 outline-none focus:border-indigo-500 text-xs"
                required
              />
            </div>
          </div>

          <button
            type="submit"
            className="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg font-bold transition-all cursor-pointer"
          >
            Update GST Registration
          </button>
        </form>
      </div>

      {/* Bank Coordinates Settings */}
      <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-sm space-y-4">
        <h3 className="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">
          Bank Transaction Coordinates Settings
        </h3>

        <form onSubmit={handleSaveBank} className="space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-xl">
            <div className="space-y-1">
              <label className="font-semibold text-slate-600">Beneficiary Bank Name</label>
              <input
                value={bankName}
                onChange={e => setBankName(e.target.value)}
                placeholder="e.g. Commonwealth Bank"
                className="w-full border border-slate-200 rounded p-2 outline-none focus:border-indigo-500 text-xs"
                required
              />
            </div>
            <div className="space-y-1">
              <label className="font-semibold text-slate-600">Account Holder Name</label>
              <input
                value={accountHolderName}
                onChange={e => setAccountHolderName(e.target.value)}
                placeholder="e.g. Logistics Pty Ltd"
                className="w-full border border-slate-200 rounded p-2 outline-none focus:border-indigo-500 text-xs"
                required
              />
            </div>
            <div className="space-y-1">
              <label className="font-semibold text-slate-600">Account Number</label>
              <input
                value={accountNumber}
                onChange={e => setAccountNumber(e.target.value)}
                placeholder="e.g. 12345678"
                className="w-full border border-slate-200 rounded p-2 outline-none focus:border-indigo-500 font-mono text-xs"
                required
              />
            </div>
            <div className="space-y-1">
              <label className="font-semibold text-slate-600">Routing Number / BSB</label>
              <input
                value={routingNumber}
                onChange={e => setRoutingNumber(e.target.value)}
                placeholder="e.g. 062-900"
                className="w-full border border-slate-200 rounded p-2 outline-none focus:border-indigo-500 font-mono text-xs"
              />
            </div>
          </div>

          <button
            type="submit"
            className="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg font-bold transition-all cursor-pointer"
          >
            Update Bank Coordinates
          </button>
        </form>
      </div>

      {/* Danger Zone */}
      <div className="bg-rose-50/50 border border-rose-200 rounded-xl p-6 shadow-sm space-y-4">
        <h3 className="text-sm font-bold text-rose-800 uppercase tracking-wider border-b border-rose-100 pb-2">
          Danger Zone Actions
        </h3>
        <p className="text-slate-500">
          Permanently delete this provider account. All distribution records, branches and employee staff files will be deleted. This action is irreversible.
        </p>

        <button
          onClick={handleDelete}
          className="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg font-bold transition-all cursor-pointer shadow-sm"
        >
          Permanently Delete Provider Profile
        </button>
      </div>
    </div>
  );
};
export default ProviderSettingsTab;
