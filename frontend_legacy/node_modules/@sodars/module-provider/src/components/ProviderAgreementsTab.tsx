import React, { useState } from 'react';
import { useCompliance } from '../hooks/useCompliance';
import { SodarsIcon } from '@sodars/icons';

interface ProviderAgreementsTabProps {
  providerId: string;
}

export const ProviderAgreementsTab: React.FC<ProviderAgreementsTabProps> = ({ providerId }) => {
  const { agreements, isLoading, createAgreement, expireAgreement } = useCompliance(providerId);
  const [showAddForm, setShowAddForm] = useState(false);
  const [agreementType, setAgreementType] = useState('Standard Service Agreement');
  const [termDays, setTermDays] = useState('365');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!agreementType) return;
    try {
      const now = Date.now();
      const expiresAt = termDays ? now + parseInt(termDays) * 24 * 60 * 60 * 1000 : now + 365 * 24 * 60 * 60 * 1000;
      await createAgreement({
        id: `agr-${Date.now()}`,
        providerId,
        title: agreementType,
        file: {
          id: `file-${Date.now()}`,
          filename: `${agreementType}.pdf`,
          fileUrl: 'https://storage.sodars.com/agreements/dummy.pdf',
          mimeType: 'application/pdf',
          sizeBytes: 1024 * 500, // 500kb
        },
        signedAt: now,
        expiresAt,
        version: 1,
        isActive: true,
        createdAt: now,
        updatedAt: now,
      });
      setShowAddForm(false);
    } catch (err) {
      alert(err instanceof Error ? err.message : String(err));
    }
  };

  if (isLoading) {
    return <div className="text-slate-400 text-xs py-8 text-center font-sans">Loading agreements safe...</div>;
  }

  const now = Date.now();

  return (
    <div className="space-y-6 font-sans">
      <div className="flex justify-between items-center">
        <div>
          <h3 className="text-base font-bold text-slate-900">Signed Service Agreements</h3>
          <p className="text-xs text-slate-500 mt-0.5">Track legally binding corporate provider SLA terms and contracts.</p>
        </div>
        <button
          onClick={() => setShowAddForm(true)}
          className="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition-all flex items-center space-x-1 cursor-pointer"
        >
          <span>+ Create Agreement</span>
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {agreements.map(a => {
          const isExpired = a.expiresAt && a.expiresAt < now;
          return (
            <div key={a.id} className="bg-white border border-slate-205 border-slate-200 rounded-xl p-5 shadow-sm space-y-4 hover:shadow-md transition-shadow relative flex flex-col justify-between min-h-[140px]">
              <div className="flex justify-between items-start">
                <div className="flex items-start space-x-3">
                  <div className={`p-2.5 rounded-lg flex items-center justify-center ${isExpired ? 'bg-rose-50 text-rose-600' : 'bg-indigo-50 text-indigo-600'}`}>
                    <SodarsIcon name="audit" size={20} />
                  </div>
                  <div>
                    <h4 className="text-xs font-bold text-slate-900">{a.title}</h4>
                    <p className="text-[10px] text-slate-450 mt-0.5">Signed {a.signedAt ? new Date(a.signedAt).toLocaleDateString() : 'N/A'}</p>
                  </div>
                </div>
                <div>
                  <span className={`px-2 py-0.5 rounded text-[9px] font-bold border ${
                    isExpired
                      ? 'bg-rose-50 text-rose-700 border-rose-100'
                      : 'bg-emerald-50 text-emerald-700 border-emerald-100'
                  }`}>
                    {isExpired ? 'Expired' : 'Active'}
                  </span>
                </div>
              </div>

              <div className="flex items-center justify-between text-[10px] text-slate-500 pt-3 border-t border-slate-50 font-medium mt-4">
                <span>
                  Expires: {a.expiresAt ? new Date(a.expiresAt).toLocaleDateString() : 'Never'}
                </span>
                {!isExpired && a.expiresAt && (
                  <button
                    onClick={() => expireAgreement(a.id)}
                    className="text-amber-600 hover:text-amber-800 font-bold uppercase cursor-pointer"
                  >
                    Expire Agreement
                  </button>
                )}
              </div>
            </div>
          );
        })}

        {agreements.length === 0 && (
          <div className="col-span-2 text-center py-12 bg-slate-50 border border-dashed border-slate-200 rounded-xl text-slate-400 text-xs italic">
            No signed agreements recorded yet.
          </div>
        )}
      </div>

      {showAddForm && (
        <div className="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-white rounded-xl shadow-xl max-w-sm w-full border border-slate-250 p-6 space-y-4">
            <div className="flex justify-between items-center border-b border-slate-100 pb-3">
              <h3 className="text-sm font-bold text-slate-900 uppercase tracking-wider">Execute SLA Agreement</h3>
              <button onClick={() => setShowAddForm(false)} className="text-slate-400 hover:text-slate-655 cursor-pointer">✕</button>
            </div>

            <form onSubmit={handleSubmit} className="space-y-4 text-xs">
              <div className="space-y-1">
                <label className="font-semibold text-slate-600">Agreement Type Name</label>
                <input
                  value={agreementType}
                  onChange={e => setAgreementType(e.target.value)}
                  placeholder="e.g. Master Service SLA 2026"
                  className="w-full border border-slate-200 rounded p-2 outline-none focus:border-indigo-500 text-xs"
                  required
                />
              </div>

              <div className="space-y-1">
                <label className="font-semibold text-slate-600">Agreement Term</label>
                <select
                  value={termDays}
                  onChange={e => setTermDays(e.target.value)}
                  className="w-full border border-slate-200 rounded p-2 outline-none focus:border-indigo-500 bg-white text-xs"
                >
                  <option value="90">90 Days (Quarterly Pilot)</option>
                  <option value="180">180 Days (Half-Yearly)</option>
                  <option value="365">1 Year (365 Days)</option>
                  <option value="1095">3 Years (Enterprise Lockin)</option>
                  <option value="">Never Expires</option>
                </select>
              </div>

              <button
                type="submit"
                className="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg font-bold transition-all text-xs cursor-pointer"
              >
                Sign & Bind Agreement
              </button>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
export default ProviderAgreementsTab;
