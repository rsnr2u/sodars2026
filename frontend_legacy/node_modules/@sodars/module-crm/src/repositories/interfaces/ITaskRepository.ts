import { Task } from '../../types';

export interface ITaskRepository {
  fetchTasks(): Promise<Task[]>;
  saveTask(task: Task): Promise<Task>;
}
