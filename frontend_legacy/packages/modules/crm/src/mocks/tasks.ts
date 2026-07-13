import { Task } from '../types';

export const mockTasks: Task[] = [
  { id: 'tsk_1', title: 'Prepare quotation proposal for Stark Ind.', priority: 'High', isCompleted: false, dueDate: Date.now() + 86400000 },
  { id: 'tsk_2', title: 'Call Acme Corp. billing manager regarding payment details', priority: 'Medium', isCompleted: true, dueDate: Date.now() - 3600000 },
  { id: 'tsk_3', title: 'Upload campaign analytics report pdf', priority: 'Low', isCompleted: false, dueDate: Date.now() + 86400000 * 3 }
];
export default mockTasks;
