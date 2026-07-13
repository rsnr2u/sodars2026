import { Role } from '../types/role';

export const mockRoles: Role[] = [
  { id: 'admin', name: 'System Administrator', permissions: ['*'], description: 'Root levels access controls policies' },
  { id: 'manager', name: 'Operational Manager', permissions: ['iam.users.view', 'iam.users.update'], description: 'Manage operational workflow details' },
  { id: 'operator', name: 'Terminal Operator', permissions: ['iam.users.view'], description: 'Run standard query reports operations' }
];
export default mockRoles;
