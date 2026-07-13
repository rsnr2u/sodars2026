import { Route as TSRoute } from '@tanstack/react-router';
import { Route as protectedRoute } from './_protected';
import { useEnquiries, EnquiryService, Enquiry, SalesStage, ActivityService } from '@sodars/module-crm';
import { useState, useEffect } from 'react';
import { SodarsIcon } from '@sodars/icons';

export const Route = new TSRoute({
  getParentRoute: () => protectedRoute,
  path: '/crm/enquiries',
  component: EnquiriesPipelineComponent,
});

const PIPELINE_STAGES: SalesStage[] = ['New', 'Assigned', 'Contacted', 'Proposal', 'Negotiation', 'Won', 'Lost'];

function EnquiriesPipelineComponent() {
  const { data: rawEnquiries, isLoading, refresh } = useEnquiries();
  const [enquiries, setEnquiries] = useState<Enquiry[]>([]);
  const [selectedEnquiry, setSelectedEnquiry] = useState<Enquiry | null>(null);
  const [newNote, setNewNote] = useState('');

  useEffect(() => {
    if (rawEnquiries) {
      setEnquiries(rawEnquiries);
    }
  }, [rawEnquiries]);

  const handleStageTransition = async (enquiryId: string, nextStage: SalesStage) => {
    try {
      const updated = await EnquiryService.updateStage(enquiryId, nextStage);
      setEnquiries(prev => prev.map(e => e.id === enquiryId ? updated : e));
      if (selectedEnquiry && selectedEnquiry.id === enquiryId) {
        setSelectedEnquiry(updated);
      }
      refresh();
    } catch (err) {
      alert(err instanceof Error ? err.message : String(err));
    }
  };

  const handleAddActivityLog = async () => {
    if (!selectedEnquiry || newNote.trim() === '') return;
    try {
      const logged = await ActivityService.logActivity(selectedEnquiry.id, 'Note', newNote.trim());
      const updatedEnquiry = {
        ...selectedEnquiry,
        activities: [...selectedEnquiry.activities, logged]
      };
      // Save locally
      await EnquiryService.createEnquiry(updatedEnquiry);
      setSelectedEnquiry(updatedEnquiry);
      setEnquiries(prev => prev.map(e => e.id === selectedEnquiry.id ? updatedEnquiry : e));
      setNewNote('');
      refresh();
    } catch (err) {
      console.error('[AddActivity] Error logging note:', err);
    }
  };

  return (
    <div className="space-y-6 font-sans relative">
      <div className="flex items-center justify-between border-b border-slate-200 pb-4">
        <div>
          <h2 className="text-2xl font-bold tracking-tight text-slate-900 flex items-center">
            <SodarsIcon name="dashboard" className="text-indigo-600 mr-2.5" size={24} />
            Lead Enquiries Pipeline
          </h2>
          <p className="text-slate-500 text-sm">Interactive sales pipeline board. Review details, log callback updates, and coordinate sales stages.</p>
        </div>
      </div>

      {isLoading ? (
        <div className="text-center py-12 text-slate-400 text-xs">Loading sales lead boards...</div>
      ) : (
        <div className="flex overflow-x-auto pb-4 gap-6 scrollbar-thin">
          {PIPELINE_STAGES.map(stage => {
            const list = enquiries.filter(e => e.stage === stage);
            return (
              <div key={stage} className="flex-shrink-0 w-72 bg-slate-50 p-4 rounded-xl border border-slate-100 flex flex-col max-h-[600px]">
                <div className="flex justify-between items-center mb-3">
                  <span className="font-bold text-xs uppercase tracking-wider text-slate-650">{stage}</span>
                  <span className="px-2 py-0.5 bg-slate-200 text-slate-600 rounded-full text-[9px] font-extrabold">{list.length}</span>
                </div>
                
                {/* Cards wrapper */}
                <div className="space-y-3 overflow-y-auto flex-1 pr-1">
                  {list.map(e => (
                    <div
                      key={e.id}
                      onClick={() => setSelectedEnquiry(e)}
                      className="p-4 bg-white border border-slate-200 rounded-lg shadow-sm hover:shadow-md transition-shadow cursor-pointer space-y-3"
                    >
                      <div className="text-[11px] font-bold text-slate-900 leading-snug">{e.name}</div>
                      <div className="flex items-center justify-between text-[10px] text-slate-500 font-semibold">
                        <span>Source: {e.source}</span>
                        <span className="text-indigo-600 font-extrabold">${e.value.toLocaleString()}</span>
                      </div>
                      <div className="flex flex-wrap gap-1">
                        {e.tags.map(t => (
                          <span key={t} className="px-1.5 py-0.5 bg-indigo-50 text-indigo-700 rounded text-[8px] font-bold uppercase">{t}</span>
                        ))}
                      </div>
                    </div>
                  ))}
                  {list.length === 0 && (
                    <div className="text-center py-8 text-slate-400 text-[10px] border border-dashed border-slate-200 rounded-lg">
                      No Leads in this stage
                    </div>
                  )}
                </div>
              </div>
            );
          })}
        </div>
      )}

      {/* Slide timeline detail drawer */}
      {selectedEnquiry && (
        <div className="fixed inset-y-0 right-0 w-96 bg-white shadow-2xl border-l border-slate-200 z-50 p-6 flex flex-col animate-slide-in font-sans">
          <div className="flex justify-between items-start border-b border-slate-100 pb-4 mb-4">
            <div>
              <span className="px-2 py-0.5 bg-indigo-100 text-indigo-700 text-[9px] rounded font-bold uppercase">{selectedEnquiry.stage}</span>
              <h3 className="text-sm font-bold text-slate-900 mt-1">{selectedEnquiry.name}</h3>
              <p className="text-[10px] text-slate-400 font-mono mt-0.5">{selectedEnquiry.email}</p>
            </div>
            <button
              onClick={() => setSelectedEnquiry(null)}
              className="text-slate-400 hover:text-slate-600 font-bold text-sm cursor-pointer"
            >
              ✕
            </button>
          </div>

          {/* Pipeline Stage Transitions Selector */}
          <div className="space-y-1.5 mb-4 bg-slate-50 p-3 rounded-lg text-xs">
            <span className="font-semibold text-slate-500 text-[10px] uppercase">Transition Sales Stage</span>
            <div className="flex flex-wrap gap-1.5 mt-1">
              {PIPELINE_STAGES.filter(stage => stage !== selectedEnquiry.stage).map(stage => (
                <button
                  key={stage}
                  onClick={() => handleStageTransition(selectedEnquiry.id, stage)}
                  className="px-2.5 py-1 bg-white hover:bg-slate-100 border border-slate-200 rounded text-[9px] font-bold text-slate-700 cursor-pointer"
                >
                  {stage}
                </button>
              ))}
            </div>
          </div>

          {/* Activities log Timeline */}
          <div className="flex-1 overflow-y-auto space-y-4 mb-4">
            <span className="font-semibold text-slate-500 text-[10px] uppercase block border-b border-slate-100 pb-1.5">Timeline Activity Logs</span>
            <div className="space-y-3">
              {selectedEnquiry.activities.map(a => (
                <div key={a.id} className="p-3 bg-slate-50 rounded-lg text-xs space-y-1">
                  <div className="flex justify-between text-[9px] font-bold text-slate-400">
                    <span>{a.type}</span>
                    <span>{new Date(a.timestamp).toLocaleDateString()}</span>
                  </div>
                  <p className="text-slate-700">{a.details}</p>
                </div>
              ))}
              {selectedEnquiry.activities.length === 0 && (
                <div className="text-center py-6 text-slate-400 text-[10px]">No activities logged yet.</div>
              )}
            </div>
          </div>

          {/* Note Input */}
          <div className="space-y-2 border-t border-slate-100 pt-4">
            <textarea
              value={newNote}
              onChange={(e) => setNewNote(e.target.value)}
              placeholder="Log note details..."
              className="w-full border border-slate-200 rounded-lg p-2.5 text-xs outline-none focus:border-indigo-500 min-h-[60px]"
            />
            <button
              onClick={handleAddActivityLog}
              className="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-bold transition-colors cursor-pointer"
            >
              Log Activity Note
            </button>
          </div>
        </div>
      )}
    </div>
  );
}
export default EnquiriesPipelineComponent;
