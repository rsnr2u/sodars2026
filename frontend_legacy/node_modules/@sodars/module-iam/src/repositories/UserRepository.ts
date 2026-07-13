import { apiClient } from '@sodars/api';
import { User } from '../types/user';
import { mockUsers } from '../mocks/users';

export class UserRepository {
  public static async fetchUsers(): Promise<User[]> {
    try {
      // In development/testing, try mock or live APIs
      const response = await apiClient.get('/iam/users');
      return response.data as User[];
    } catch (err) {
      console.warn('[UserRepository] HTTP request failed, falling back to mock database:', err);
      return mockUsers;
    }
  }

  public static async findUser(id: string): Promise<User | null> {
    try {
      const response = await apiClient.get(`/iam/users/${id}`);
      return response.data as User;
    } catch (err) {
      console.warn('[UserRepository] HTTP request failed, falling back to mock finder:', err);
      return mockUsers.find(u => u.id === id) || null;
    }
  }
}
export default UserRepository;
