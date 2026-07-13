import { Route as TSRoute } from '@tanstack/react-router';
import { Route as protectedRoute } from './_protected';
import { useEnquiries, FollowUp } from '@sodars/module-crm';
import { useState, useEffect } from 'react';
import { SodarsIcon } from '@sodars/icons';

export const Route = new TSRoute({
  getParentRoute: () => protectedRoute,
  path: '/crm/calendar',
  component: CalendarPageComponent,
});

function CalendarPageComponent() {
  const { data: enquiries, isLoading } = useEnquiries();
  const [events, setEvents] = useState<FollowUp[]>([]);

  useEffect(() => {
    if (enquiries) {
      const all: FollowUp[] = [];
      enquiries.forEach(e => {
        if (e.followUps) {
          all.push(...e.followUps);
        }
      });
      setEvents(all);
    }
  }, [enquiries]);

  return (
    <div className="space-y-6 font-sans">
      <div className="flex items-center justify-between border-b border-slate-200 pb-4">
        <div>
          <h2 className="text-2xl font-bold tracking-tight text-slate-900 flex items-center">
            <SodarsIcon name="dashboard" className="text-indigo-600 mr-2.5" size={24} />
            CRM Action Calendar
          </h2>
          <p className="text-slate-500 text-sm">Schedule grid mapping callback follow-ups, timeline updates, and appointments.</p>
        </div>
      </div>

      <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
        {isLoading ? (
          <div className="text-slate-400 text-xs py-4 text-center">Loading calendars...</div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-7 border border-slate-200 divide-x divide-y divide-slate-100 text-xs rounded-xl overflow-hidden">
            {['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].map(day => (
              <div key={day} className="bg-slate-50 p-3 font-semibold text-center text-slate-500">{day}</div>
            ))}
            {/* Simple calendar month cells mocks */}
            {Array.from({ length: 28 }).map((_, idx) => {
              const dayNum = idx + 1;
              const dayEvents = events.filter(e => new Date(e.scheduledAt).getDate() === dayNum);
              return (
                <div key={idx} className="p-3 min-h-[100px] bg-white flex flex-col justify-between">
                  <span className="font-bold text-slate-400">{dayNum}</span>
                  <div className="space-y-1 mt-2">
                    {dayEvents.map(ev => (
                      <div key={ev.id} className="p-1 bg-indigo-50 border border-indigo-100 text-indigo-700 rounded text-[9px] font-semibold truncate">
                        {ev.description}
                      </div>
                    ))}
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </div>
    </div>
  );
}
export default CalendarPageComponent;
