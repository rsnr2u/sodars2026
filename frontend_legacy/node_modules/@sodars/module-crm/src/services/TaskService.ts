import { MockTaskRepository } from '../repositories/MockTaskRepository';
import { Task } from '../types';

export class TaskService {
  private static repository = new MockTaskRepository();

  public static async getTasks(): Promise<Task[]> {
    return this.repository.fetchTasks();
  }

  public static async addTask(title: string, priority: 'Low' | 'Medium' | 'High'): Promise<Task> {
    const task: Task = {
      id: `tsk_${Date.now()}`,
      title,
      priority,
      isCompleted: false,
      dueDate: Date.now() + 86400000
    };
    return this.repository.saveTask(task);
  }

  public static async toggleTaskComplete(task: Task): Promise<Task> {
    const updated: Task = {
      ...task,
      isCompleted: !task.isCompleted
    };
    return this.repository.saveTask(updated);
  }
}
export default TaskService;
