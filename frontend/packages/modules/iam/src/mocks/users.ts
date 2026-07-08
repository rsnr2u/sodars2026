import { User } from '../types/user';

export const mockUsers: User[] = [
  { id: 'usr_1', name: 'John Doe', email: 'john.doe@sodars.com', roleId: 'admin', isLocked: false },
  { id: 'usr_2', name: 'Jane Smith', email: 'jane.smith@sodars.com', roleId: 'operator', isLocked: false },
  { id: 'usr_3', name: 'Alice Johnson', email: 'alice.johnson@sodars.com', roleId: 'manager', isLocked: true },
  { id: 'usr_4', name: 'Bob Brown', email: 'bob.brown@sodars.com', roleId: 'viewer', isLocked: false }
];
export default mockUsers;
