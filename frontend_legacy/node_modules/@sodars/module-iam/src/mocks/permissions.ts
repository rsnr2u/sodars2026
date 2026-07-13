import { Permission } from '../types/permission';

export const mockPermissions: Permission[] = [
  { id: 'iam.users.view', name: 'View Users', resource: 'users', action: 'view', description: 'Allows reading user profiles' },
  { id: 'iam.users.create', name: 'Create Users', resource: 'users', action: 'create', description: 'Allows creating new user profiles' },
  { id: 'iam.users.update', name: 'Update Users', resource: 'users', action: 'update', description: 'Allows editing user profiles' },
  { id: 'iam.users.delete', name: 'Delete Users', resource: 'users', action: 'delete', description: 'Allows removing user profiles' }
];
export default mockPermissions;
