import { UserService } from '../services/UserService';
import { mockUsers } from '../mocks/users';

describe('UserService Unit Tests', () => {
  it('should filter out locked accounts when fetching active users', async () => {
    const activeUsers = await UserService.getActiveUsers();
    expect(activeUsers).toBeDefined();
    const hasLocked = activeUsers.some(u => u.isLocked);
    expect(hasLocked).toBe(false);
  });

  it('should set isLocked flag to true when locking a user account', async () => {
    const user = mockUsers[0];
    const lockedUser = await UserService.lockUserAccount(user);
    expect(lockedUser.isLocked).toBe(true);
  });
});
