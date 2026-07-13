import { CommandRegistry } from '@sodars/sdk';
import { IAM_MODULE_ID } from '../constants/module';

export const registerCommands = (commands: typeof CommandRegistry): void => {
  const commandsList = [
    { id: 'cmd.iam.dashboard', title: 'Open IAM Dashboard', route: '/iam', keywords: ['iam', 'dashboard', 'access control'] },
    { id: 'cmd.iam.users', title: 'Open Users Directory', route: '/iam/users', keywords: ['users', 'directory', 'accounts'] },
    { id: 'cmd.iam.user.create', title: 'Create User Account', route: '/iam/users/new', keywords: ['create user', 'add account', 'invite user'] },
    { id: 'cmd.iam.roles', title: 'Open Roles & Policies', route: '/iam/roles', keywords: ['roles', 'policies', 'groups'] },
    { id: 'cmd.iam.role.create', title: 'Create Custom Role', route: '/iam/roles/new', keywords: ['create role', 'add group'] },
    { id: 'cmd.iam.permissions', title: 'Open Permissions Matrix', route: '/iam/permissions', keywords: ['permissions', 'rules', 'scopes'] },
    { id: 'cmd.iam.sessions', title: 'View Active Sessions', route: '/iam/sessions', keywords: ['sessions', 'logins', 'devices'] },
    { id: 'cmd.iam.audit', title: 'View Audit Logs', route: '/iam/audit', keywords: ['audit', 'logs', 'history', 'activities'] },
    { id: 'cmd.iam.tokens', title: 'View API Tokens', route: '/iam/tokens', keywords: ['api tokens', 'keys', 'client credentials'] }
  ];

  for (const cmd of commandsList) {
    commands.register({
      id: cmd.id,
      module: IAM_MODULE_ID,
      title: cmd.title,
      keywords: cmd.keywords,
      group: 'Identity & Access Management',
      order: 10,
      execute: (ctx) => {
        ctx.router.navigate(cmd.route);
      }
    });
  }
};
export default registerCommands;
