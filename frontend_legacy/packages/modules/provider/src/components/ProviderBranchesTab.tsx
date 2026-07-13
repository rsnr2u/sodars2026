import React, { useState } from 'react';
import { useBranches } from '../hooks/useBranches';
import { BranchStatus } from '../enums';
import { SodarsIcon } from '@sodars/icons';

interface ProviderBranchesTabProps {
  providerId: string;
}

export const ProviderBranchesTab: React.FC<ProviderBranchesTabProps> = ({ providerId }) => {
  const { data: branches, isLoading, createBranch, deleteBranch } = useBranches(providerId);
  const [showCreateModal, setShowCreateModal] = useState(false);
  const [name, setName] = useState('');
  const [street, setStreet] = useState('');
  const [city, setCity] = useState('');
  const [state, setState] = useState('');
  const [zipCode, setZipCode] = useState('');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!name) return;
    try {
      await createBranch({
        id: `branch-${Date.now()}`,
        providerId,
        name,
        status: BranchStatus.Active,
        address: { street, city, state, zipCode, country: 'AU', isBilling: false },
        phone: { countryCode: '61', number: '000000000' },
        email: { value: 'branch@corporate.com' },
        isMainBranch: false,
        createdAt: Date.now(),
        updatedAt: Date.now(),
        version: 1,
        isActive: true,
      });
      setName('');
      setStreet('');
      setCity('');
      setState('');
      setZipCode('');
      setShowCreateModal(false);
    } catch (err) {
      alert(err instanceof Error ? err.message : String(err));
    }
  };

  const getStatusBadge = (status: BranchStatus) => {
    switch (status) {
      case BranchStatus.Active:
        return 'bg-emerald-50 text-emerald-700 border-emerald-100';
      case BranchStatus.Inactive:
        return 'bg-slate-50 text-slate-605 text-slate-600 border-slate-100';
      default:
        return 'bg-rose-50 text-rose-700 border-rose-100';
    }
  };

  if (isLoading) {
    return <div className="text-slate-400 text-xs py-8 text-center">Loading branch directory list...</div>;
  }

  return (
    <div className="space-y-6 font-sans">
      <div className="flex justify-between items-center">
        <div>
          <h3 className="text-base font-bold text-slate-900">Registered Branch coordinates</h3>
          <p className="text-xs text-slate-500 mt-0.5">Manage regional service branches and distribution centers.</p>
        </div>
        <button
          onClick={() => setShowCreateModal(true)}
          className="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition-all flex items-center space-x-1 cursor-pointer"
        >
          <span>+ Add Branch</span>
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {branches.map(b => (
          <div key={b.id} className="bg-white border border-slate-200 rounded-xl p-5 shadow-sm space-y-4 hover:shadow-md transition-shadow relative">
            <div className="flex justify-between items-start">
              <div>
                <span className="font-mono text-[9px] uppercase tracking-wider text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded font-bold">BRANCH</span>
                <h4 className="text-sm font-bold text-slate-900 mt-1.5">{b.name}</h4>
              </div>
              <div className="flex items-center space-x-2">
                <span className={`px-2 py-0.5 rounded text-[10px] font-bold border ${getStatusBadge(b.status)}`}>
                  {b.status}
                </span>
                <button
                  onClick={() => deleteBranch(b.id)}
                  className="text-slate-450 hover:text-rose-600 text-xs transition-colors cursor-pointer font-bold"
                  title="Delete Branch"
                >
                  ✕
                </button>
              </div>
            </div>

            <div className="text-xs text-slate-600 space-y-1 pt-1.5 border-t border-slate-50">
              <div className="flex items-center space-x-1.5">
                <SodarsIcon name="settings" className="text-slate-400" size={13} />
                <span>{b.address.street}, {b.address.city}, {b.address.state} {b.address.zipCode}</span>
              </div>
            </div>
          </div>
        ))}

        {branches.length === 0 && (
          <div className="col-span-2 text-center py-12 bg-slate-50 border border-dashed border-slate-200 rounded-xl text-slate-400 text-xs italic">
            No branch offices registered yet.
          </div>
        )}
      </div>

      {showCreateModal && (
        <div className="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-white rounded-xl shadow-xl max-w-md w-full border border-slate-250 p-6 space-y-4">
            <div className="flex justify-between items-center border-b border-slate-100 pb-3">
              <h3 className="text-sm font-bold text-slate-900 uppercase tracking-wider">Add New Branch Office</h3>
              <button onClick={() => setShowCreateModal(false)} className="text-slate-400 hover:text-slate-655 cursor-pointer">✕</button>
            </div>

            <form onSubmit={handleSubmit} className="space-y-4 text-xs">
              <div className="space-y-1">
                <label className="font-semibold text-slate-600">Branch Name</label>
                <input
                  value={name}
                  onChange={e => setName(e.target.value)}
                  placeholder="e.g. Sydney Central Office"
                  className="w-full border border-slate-200 rounded p-2 outline-none focus:border-indigo-500 text-xs"
                  required
                />
              </div>

              <div className="space-y-1">
                <label className="font-semibold text-slate-600">Street Address</label>
                <input
                  value={street}
                  onChange={e => setStreet(e.target.value)}
                  placeholder="e.g. 100 George St"
                  className="w-full border border-slate-200 rounded p-2 outline-none focus:border-indigo-500 text-xs"
                />
              </div>

              <div className="grid grid-cols-3 gap-3">
                <div className="space-y-1">
                  <label className="font-semibold text-slate-600">City</label>
                  <input
                    value={city}
                    onChange={e => setCity(e.target.value)}
                    placeholder="Sydney"
                    className="w-full border border-slate-200 rounded p-2 outline-none focus:border-indigo-500 text-xs"
                  />
                </div>
                <div className="space-y-1">
                  <label className="font-semibold text-slate-600">State</label>
                  <input
                    value={state}
                    onChange={e => setState(e.target.value)}
                    placeholder="NSW"
                    className="w-full border border-slate-200 rounded p-2 outline-none focus:border-indigo-500 text-xs"
                  />
                </div>
                <div className="space-y-1">
                  <label className="font-semibold text-slate-600">Zip Code</label>
                  <input
                    value={zipCode}
                    onChange={e => setZipCode(e.target.value)}
                    placeholder="2000"
                    className="w-full border border-slate-200 rounded p-2 outline-none focus:border-indigo-500 text-xs"
                  />
                </div>
              </div>

              <button
                type="submit"
                className="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg font-bold transition-all text-xs cursor-pointer"
              >
                Register Branch Coordinates
              </button>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
export default ProviderBranchesTab;
