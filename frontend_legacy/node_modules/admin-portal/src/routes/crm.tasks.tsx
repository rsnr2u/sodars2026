import { Route as TSRoute } from '@tanstack/react-router';
import { Route as protectedRoute } from './_protected';
import { TaskService, Task } from '@sodars/module-crm';
import { useState, useEffect } from 'react';
import { SodarsIcon } from '@sodars/icons';

export const Route = new TSRoute({
  getParentRoute: () => protectedRoute,
  path: '/crm/tasks',
  component: TasksPageComponent,
});

function TasksPageComponent() {
  const [tasks, setTasks] = useState<Task[]>([]);
  const [loading, setLoading] = useState(true);
  const [newTaskTitle, setNewTaskTitle] = useState('');
  const [newTaskPriority, setNewTaskPriority] = useState<'Low' | 'Medium' | 'High'>('Medium');

  const fetchTasksList = () => {
    setLoading(true);
    TaskService.getTasks()
      .then(res => {
        setTasks(res);
        setLoading(false);
      })
      .catch(() => setLoading(false));
  };

  useEffect(() => {
    fetchTasksList();
  }, []);

  const handleToggleComplete = async (task: Task) => {
    try {
      await TaskService.toggleTaskComplete(task);
      fetchTasksList();
    } catch (err) {
      console.error('[TasksPage] Error toggling complete:', err);
    }
  };

  const handleAddTask = async (e: React.FormEvent) => {
    e.preventDefault();
    if (newTaskTitle.trim() === '') return;
    try {
      await TaskService.addTask(newTaskTitle.trim(), newTaskPriority);
      setNewTaskTitle('');
      fetchTasksList();
    } catch (err) {
      console.error('[TasksPage] Error adding task:', err);
    }
  };

  return (
    <div className="space-y-6 font-sans">
      <div className="flex items-center justify-between border-b border-slate-200 pb-4">
        <div>
          <h2 className="text-2xl font-bold tracking-tight text-slate-900 flex items-center">
            <SodarsIcon name="dashboard" className="text-indigo-600 mr-2.5" size={24} />
            CRM Tasks Manager
          </h2>
          <p className="text-slate-500 text-sm">Organize client interactions checklists, status follow-ups, and sales task lists.</p>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-12 gap-6">
        {/* Create task form */}
        <div className="col-span-12 md:col-span-4 p-6 bg-white border border-slate-200 rounded-xl shadow-sm h-fit">
          <h3 className="text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">
            Create CRM Task
          </h3>
          <form onSubmit={handleAddTask} className="space-y-4 text-xs">
            <div className="space-y-1.5">
              <label className="font-semibold text-slate-500 uppercase text-[10px]">Task Title</label>
              <input
                type="text"
                value={newTaskTitle}
                onChange={(e) => setNewTaskTitle(e.target.value)}
                placeholder="Enter task details..."
                className="w-full border border-slate-200 rounded-lg p-2.5 outline-none focus:border-indigo-500"
              />
            </div>
            <div className="space-y-1.5">
              <label className="font-semibold text-slate-500 uppercase text-[10px]">Priority Stage</label>
              <select
                value={newTaskPriority}
                onChange={(e) => setNewTaskPriority(e.target.value as any)}
                className="w-full border border-slate-200 rounded-lg p-2.5 outline-none focus:border-indigo-500 bg-white"
              >
                <option value="Low">Low Priority</option>
                <option value="Medium">Medium Priority</option>
                <option value="High">High Priority</option>
              </select>
            </div>
            <button
              type="submit"
              className="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-bold transition-colors cursor-pointer"
            >
              Add Task Checklist
            </button>
          </form>
        </div>

        {/* Tasks directories list */}
        <div className="col-span-12 md:col-span-8 p-6 bg-white border border-slate-200 rounded-xl shadow-sm">
          <h3 className="text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">
            Tasks Checklist Index
          </h3>
          {loading ? (
            <div className="text-slate-400 text-xs py-4 text-center">Loading task indexes...</div>
          ) : (
            <div className="space-y-3">
              {tasks.map(t => (
                <div key={t.id} className="flex items-center justify-between p-3.5 bg-slate-50 hover:bg-slate-100 rounded-lg transition-colors text-xs">
                  <div className="flex items-center space-x-3">
                    <input
                      type="checkbox"
                      checked={t.isCompleted}
                      onChange={() => handleToggleComplete(t)}
                      className="w-4 h-4 text-indigo-650 border-slate-300 rounded focus:ring-indigo-500 cursor-pointer"
                    />
                    <span className={`text-slate-800 font-medium ${t.isCompleted ? 'line-through text-slate-400' : ''}`}>{t.title}</span>
                  </div>
                  <div className="flex items-center space-x-3">
                    <span className={`px-2 py-0.5 rounded font-bold text-[9px] uppercase ${t.priority === 'High' ? 'bg-red-100 text-red-700' : t.priority === 'Medium' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700'}`}>
                      {t.priority}
                    </span>
                  </div>
                </div>
              ))}
              {tasks.length === 0 && (
                <div className="text-center py-8 text-slate-400 text-xs">No tasks mapped in checklist.</div>
              )}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
export default TasksPageComponent;
