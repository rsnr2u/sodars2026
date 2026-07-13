import React, { useState } from 'react';
import { useVerification } from '../hooks/useVerification';
import { ProviderStatus, VerificationStepStatus, VerificationType } from '../enums';

interface ProviderVerificationTabProps {
  providerId: string;
}

export const ProviderVerificationTab: React.FC<ProviderVerificationTabProps> = ({ providerId }) => {
  const {
    data: verification,
    isLoading,
    startVerification,
    updateStepStatus,
    approveVerification,
    rejectVerification,
    restartVerification,
  } = useVerification(providerId);

  const [rejectReason, setRejectReason] = useState('');
  const [showRejectForm, setShowRejectForm] = useState(false);

  const handleStart = async () => {
    try {
      await startVerification(VerificationType.GST);
    } catch (err) {
      alert(err instanceof Error ? err.message : String(err));
    }
  };

  const handleApprove = async () => {
    if (!verification) return;
    try {
      await approveVerification(verification.id);
    } catch (err) {
      alert(err instanceof Error ? err.message : String(err));
    }
  };

  const handleReject = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!rejectReason || !verification) return;
    try {
      await rejectVerification(verification.id, rejectReason);
      setRejectReason('');
      setShowRejectForm(false);
    } catch (err) {
      alert(err instanceof Error ? err.message : String(err));
    }
  };

  const handleRestart = async () => {
    if (!verification) return;
    try {
      await restartVerification(verification.id);
    } catch (err) {
      alert(err instanceof Error ? err.message : String(err));
    }
  };

  const handleStepStatusChange = async (stepId: string, status: VerificationStepStatus) => {
    if (!verification) return;
    try {
      await updateStepStatus(verification.id, stepId, status);
    } catch (err) {
      alert(err instanceof Error ? err.message : String(err));
    }
  };

  if (isLoading) {
    return <div className="text-slate-400 text-xs py-8 text-center font-sans">Loading verification workflows...</div>;
  }

  const getStepBadgeClass = (status: VerificationStepStatus) => {
    switch (status) {
      case VerificationStepStatus.Passed:
        return 'bg-emerald-50 text-emerald-700 border-emerald-100';
      case VerificationStepStatus.Failed:
        return 'bg-rose-50 text-rose-700 border-rose-100';
      default:
        return 'bg-slate-50 text-slate-600 border-slate-100';
    }
  };

  const getOverallBadgeClass = (status: ProviderStatus) => {
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

  return (
    <div className="space-y-6 font-sans">
      <div className="bg-slate-50 border border-slate-200 rounded-xl p-5 shadow-inner flex flex-col md:flex-row justify-between items-start md:items-center gap-4 text-xs">
        <div>
          <span className="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Aggregate Verification State</span>
          <div className="flex items-center space-x-2 mt-1">
            <span className={`px-2.5 py-0.5 rounded-full font-bold border text-xs ${verification ? getOverallBadgeClass(verification.status) : 'bg-slate-100 text-slate-800 border-slate-200'}`}>
              {verification ? verification.status : ProviderStatus.Pending}
            </span>
            <span className="text-slate-500 text-[10px]">
              (Enforces State Machine Guards: Pending ➔ Under Review ➔ Verified / Rejected)
            </span>
          </div>
        </div>

        <div className="flex flex-wrap gap-2.5">
          {!verification && (
            <button
              onClick={handleStart}
              className="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold shadow-sm transition-colors cursor-pointer"
            >
              Start Verification Flow
            </button>
          )}

          {verification?.status === ProviderStatus.Pending && (
            <button
              onClick={handleStart}
              className="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold shadow-sm transition-colors cursor-pointer"
            >
              Move to Under Review
            </button>
          )}

          {verification?.status === ProviderStatus.UnderReview && (
            <>
              <button
                onClick={handleApprove}
                className="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold shadow-sm transition-colors cursor-pointer"
              >
                Approve & Verify Provider
              </button>
              <button
                onClick={() => setShowRejectForm(true)}
                className="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg font-bold shadow-sm transition-colors cursor-pointer"
              >
                Reject Provider
              </button>
            </>
          )}

          {(verification?.status === ProviderStatus.Verified || verification?.status === ProviderStatus.Rejected) && (
            <button
              onClick={handleRestart}
              className="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg font-bold shadow-sm transition-colors cursor-pointer"
            >
              Restart Verification Flow
            </button>
          )}
        </div>
      </div>

      {verification ? (
        <div className="space-y-4">
          <h3 className="text-sm font-bold text-slate-900 uppercase tracking-wider block border-b border-slate-100 pb-2">
            Verification Steps Checklist
          </h3>

          <div className="divide-y divide-slate-100 border border-slate-200 bg-white rounded-xl overflow-hidden shadow-sm">
            {verification.steps.map(step => (
              <div key={step.id} className="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs hover:bg-slate-50/50 transition-colors">
                <div className="flex items-center space-x-3">
                  <div className={`w-2 h-2 rounded-full ${
                    step.status === VerificationStepStatus.Passed
                      ? 'bg-emerald-500'
                      : step.status === VerificationStepStatus.Failed
                      ? 'bg-rose-500'
                      : 'bg-slate-400'
                  }`}></div>
                  <div>
                    <div className="font-bold text-slate-800">{step.name}</div>
                    <div className="text-[10px] text-slate-455 text-slate-400 mt-0.5">Last updated {new Date(step.updatedAt).toLocaleDateString()}</div>
                  </div>
                </div>

                <div className="flex items-center space-x-3">
                  <span className={`px-2.5 py-0.5 rounded text-[10px] font-bold border ${getStepBadgeClass(step.status)}`}>
                    {step.status}
                  </span>

                  {verification.status === ProviderStatus.UnderReview && (
                    <div className="flex space-x-1.5">
                      {step.status === VerificationStepStatus.Pending && (
                        <>
                          <button
                            onClick={() => handleStepStatusChange(step.id, VerificationStepStatus.Passed)}
                            className="px-2 py-1 bg-white hover:bg-emerald-50 border border-slate-200 text-emerald-700 hover:border-emerald-250 font-bold rounded text-[10px] cursor-pointer"
                          >
                            Pass
                          </button>
                          <button
                            onClick={() => handleStepStatusChange(step.id, VerificationStepStatus.Failed)}
                            className="px-2 py-1 bg-white hover:bg-rose-50 border border-slate-200 text-rose-700 hover:border-rose-250 font-bold rounded text-[10px] cursor-pointer"
                          >
                            Fail
                          </button>
                        </>
                      )}
                    </div>
                  )}
                </div>
              </div>
            ))}
          </div>
        </div>
      ) : (
        <div className="text-center py-12 bg-white border border-slate-200 rounded-xl text-slate-400 text-xs italic">
          Verification workflow is not initialized yet. Press "Start Verification Flow" to begin.
        </div>
      )}

      {showRejectForm && (
        <div className="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-white rounded-xl shadow-xl max-w-sm w-full border border-slate-250 p-6 space-y-4">
            <div className="flex justify-between items-center border-b border-slate-100 pb-3">
              <h3 className="text-sm font-bold text-slate-900 uppercase tracking-wider">Reject Verification Profile</h3>
              <button onClick={() => setShowRejectForm(false)} className="text-slate-400 hover:text-slate-655 cursor-pointer">✕</button>
            </div>

            <form onSubmit={handleReject} className="space-y-4 text-xs">
              <div className="space-y-1">
                <label className="font-semibold text-slate-600">Rejection Reason Description</label>
                <textarea
                  value={rejectReason}
                  onChange={e => setRejectReason(e.target.value)}
                  placeholder="Provide detailed comments on why this provider registration is rejected..."
                  className="w-full border border-slate-200 rounded p-2.5 outline-none focus:border-indigo-500 min-h-[80px]"
                  required
                />
              </div>

              <button
                type="submit"
                className="w-full py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg font-bold transition-all text-xs cursor-pointer"
              >
                Reject Registration
              </button>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
export default ProviderVerificationTab;
