import { PermissionRegistry } from '@sodars/sdk';
import { IAM_MODULE_ID } from '../constants/module';

export const registerPermissions = (permissions: typeof PermissionRegistry): void => {
  const perms = [
    { id: 'iam.users.view', resource: 'users', action: 'view', category: 'IAM', description: 'Allows reading user account profiles' },
    { id: 'iam.users.create', resource: 'users', action: 'create', category: 'IAM', description: 'Allows creating new user profiles' },
    { id: 'iam.users.update', resource: 'users', action: 'update', category: 'IAM', description: 'Allows editing user profiles' },
    { id: 'iam.users.delete', resource: 'users', action: 'delete', category: 'IAM', description: 'Allows removing user profiles' },
    { id: 'iam.roles.view', resource: 'roles', action: 'view', category: 'IAM', description: 'Allows reading role allocation tables' },
    { id: 'iam.roles.update', resource: 'roles', action: 'update', category: 'IAM', description: 'Allows updates to role details' },
    { id: 'iam.permissions.assign', resource: 'permissions', action: 'assign', category: 'IAM', description: 'Allows assigning security tokens' },
    { id: 'iam.audit.view', resource: 'audit', action: 'view', category: 'IAM', description: 'Allows viewing security audit logs' },
    { id: 'iam.sessions.revoke', resource: 'sessions', action: 'revoke', category: 'IAM', description: 'Allows revoking active login sessions' }
  ];

  perms.forEach((p, idx) => {
    permissions.register({
      id: p.id,
      module: IAM_MODULE_ID,
      resource: p.resource,
      action: p.action,
      category: p.category,
      description: p.description,
      order: idx + 1
    });
  });
};
export default registerPermissions;
