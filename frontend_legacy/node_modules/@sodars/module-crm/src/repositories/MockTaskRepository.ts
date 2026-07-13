import { ITaskRepository } from './interfaces/ITaskRepository';
import { Task } from '../types';
import { mockTasks } from '../mocks/tasks';
import { apiClient } from '@sodars/api';

export class MockTaskRepository implements ITaskRepository {
  private static database: Task[] = [...mockTasks];

  public async fetchTasks(): Promise<Task[]> {
    try {
      const response = await apiClient.get('/crm/tasks');
      return response.data as Task[];
    } catch {
      return MockTaskRepository.database;
    }
  }

  public async saveTask(task: Task): Promise<Task> {
    try {
      const response = await apiClient.post('/crm/tasks', task);
      return response.data as Task;
    } catch {
      const index = MockTaskRepository.database.findIndex(t => t.id === task.id);
      if (index >= 0) {
        MockTaskRepository.database[index] = task;
      } else {
        MockTaskRepository.database.push(task);
      }
      return task;
    }
  }
}
export default MockTaskRepository;
