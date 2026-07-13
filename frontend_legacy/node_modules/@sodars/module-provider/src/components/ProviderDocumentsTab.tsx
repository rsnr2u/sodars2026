import React, { useState } from 'react';
import { useCompliance } from '../hooks/useCompliance';
import { SodarsIcon } from '@sodars/icons';

interface ProviderDocumentsTabProps {
  providerId: string;
}

export const ProviderDocumentsTab: React.FC<ProviderDocumentsTabProps> = ({ providerId }) => {
  const { documents, isLoading, uploadDocument, expireDocument, deleteDocument } = useCompliance(providerId);
  const [showAddForm, setShowAddForm] = useState(false);
  const [docName, setDocName] = useState('');
  const [docType, setDocType] = useState('License');
  const [expiresInDays, setExpiresInDays] = useState('365');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!docName) return;
    try {
      const now = Date.now();
      const expiresAt = expiresInDays ? now + parseInt(expiresInDays) * 24 * 60 * 60 * 1000 : undefined;
      await uploadDocument({
        id: `doc-${Date.now()}`,
        providerId,
        name: docName,
        type: docType,
        file: {
          id: `file-${Date.now()}`,
          filename: `${docName}.pdf`,
          fileUrl: 'https://storage.sodars.com/docs/dummy.pdf',
          mimeType: 'application/pdf',
          sizeBytes: 1024 * 250, // 250kb
        },
        expiresAt,
        version: 1,
        isActive: true,
        createdAt: now,
        updatedAt: now,
      });
      setDocName('');
      setShowAddForm(false);
    } catch (err) {
      alert(err instanceof Error ? err.message : String(err));
    }
  };

  if (isLoading) {
    return <div className="text-slate-400 text-xs py-8 text-center">Loading documents safe...</div>;
  }

  const now = Date.now();

  return (
    <div className="space-y-6 font-sans">
      <div className="flex justify-between items-center">
        <div>
          <h3 className="text-base font-bold text-slate-900">Documents safe repository</h3>
          <p className="text-xs text-slate-500 mt-0.5">Secure regulatory certificates, licenses, and insurance policies archive.</p>
        </div>
        <button
          onClick={() => setShowAddForm(true)}
          className="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition-all flex items-center space-x-1 cursor-pointer"
        >
          <span>+ Upload Document</span>
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {documents.map(d => {
          const isExpired = d.expiresAt && d.expiresAt < now;
          return (
            <div key={d.id} className="bg-white border border-slate-200 rounded-xl p-5 shadow-sm space-y-4 hover:shadow-md transition-shadow relative">
              <div className="flex justify-between items-start">
                <div className="flex items-start space-x-3">
                  <div className={`p-2.5 rounded-lg flex items-center justify-center ${isExpired ? 'bg-rose-50 text-rose-600' : 'bg-indigo-50 text-indigo-600'}`}>
                    <SodarsIcon name="audit" size={20} />
                  </div>
                  <div>
                    <h4 className="text-xs font-bold text-slate-900">{d.name}</h4>
                    <p className="text-[10px] text-slate-455 text-slate-400 mt-0.5">Type: {d.type} • PDF Archive</p>
                  </div>
                </div>
                <div className="flex items-center space-x-2">
                  <span className={`px-2 py-0.5 rounded text-[9px] font-bold border ${
                    isExpired
                      ? 'bg-rose-50 text-rose-700 border-rose-100'
                      : 'bg-emerald-50 text-emerald-700 border-emerald-100'
                  }`}>
                    {isExpired ? 'Expired' : 'Valid'}
                  </span>
                  <button
                    onClick={() => deleteDocument(d.id)}
                    className="text-slate-400 hover:text-rose-600 text-xs transition-colors cursor-pointer"
                    title="Delete document"
                  >
                    ✕
                  </button>
                </div>
              </div>

              <div className="flex items-center justify-between text-[10px] text-slate-500 pt-3 border-t border-slate-50 font-medium">
                <span>
                  Expires: {d.expiresAt ? new Date(d.expiresAt).toLocaleDateString() : 'Never'}
                </span>
                {!isExpired && d.expiresAt && (
                  <button
                    onClick={() => expireDocument(d.id)}
                    className="text-amber-600 hover:text-amber-800 font-bold uppercase cursor-pointer"
                  >
                    Mark Expired
                  </button>
                )}
              </div>
            </div>
          );
        })}

        {documents.length === 0 && (
          <div className="col-span-2 text-center py-12 bg-slate-50 border border-dashed border-slate-200 rounded-xl text-slate-400 text-xs italic">
            No compliance certificates uploaded.
          </div>
        )}
      </div>

      {showAddForm && (
        <div className="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-white rounded-xl shadow-xl max-w-sm w-full border border-slate-250 p-6 space-y-4">
            <div className="flex justify-between items-center border-b border-slate-100 pb-3">
              <h3 className="text-sm font-bold text-slate-900 uppercase tracking-wider">Upload New Document</h3>
              <button onClick={() => setShowAddForm(false)} className="text-slate-400 hover:text-slate-655 cursor-pointer">✕</button>
            </div>

            <form onSubmit={handleSubmit} className="space-y-4 text-xs">
              <div className="space-y-1">
                <label className="font-semibold text-slate-600">Document Name / Label</label>
                <input
                  value={docName}
                  onChange={e => setDocName(e.target.value)}
                  placeholder="e.g. Liability Insurance Policy 2026"
                  className="w-full border border-slate-200 rounded p-2 outline-none focus:border-indigo-500"
                  required
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-1">
                  <label className="font-semibold text-slate-600">Document Type</label>
                  <select
                    value={docType}
                    onChange={e => setDocType(e.target.value)}
                    className="w-full border border-slate-200 rounded p-2 outline-none focus:border-indigo-500 bg-white"
                  >
                    <option value="License">Corporate License</option>
                    <option value="Insurance">Business Insurance</option>
                    <option value="Permit">Regulatory Permit</option>
                    <option value="Audit">External Audit</option>
                  </select>
                </div>
                <div className="space-y-1">
                  <label className="font-semibold text-slate-600">Validity Period</label>
                  <select
                    value={expiresInDays}
                    onChange={e => setExpiresInDays(e.target.value)}
                    className="w-full border border-slate-200 rounded p-2 outline-none focus:border-indigo-500 bg-white"
                  >
                    <option value="30">30 Days</option>
                    <option value="90">90 Days</option>
                    <option value="180">180 Days</option>
                    <option value="365">1 Year (365 Days)</option>
                    <option value="">Never Expires</option>
                  </select>
                </div>
              </div>

              <button
                type="submit"
                className="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg font-bold transition-all text-xs cursor-pointer"
              >
                Upload & Secure Document
              </button>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
export default ProviderDocumentsTab;
