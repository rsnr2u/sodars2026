import { UserRepository } from '../repositories/UserRepository';
import { mockUsers } from '../mocks/users';

describe('UserRepository Unit Tests', () => {
  it('should fetch users list successfully', async () => {
    const users = await UserRepository.fetchUsers();
    expect(users).toBeDefined();
    expect(users.length).toBeGreaterThan(0);
    expect(users[0].email).toContain('@');
  });

  it('should find user profile by ID', async () => {
    const user = await UserRepository.findUser('usr_1');
    expect(user).toBeDefined();
    expect(user?.name).toBe('John Doe');
  });

  it('should return null for non-existing users', async () => {
    const user = await UserRepository.findUser('usr_non_exist');
    expect(user).toBeNull();
  });
});
