import { RouteRegistry } from '@sodars/sdk';
import { IAM_MODULE_ID } from '../constants/module';

export const registerRoutes = (routes: typeof RouteRegistry): void => {
  const routesList = [
    { id: 'route.iam.dashboard', path: '/iam', title: 'IAM Dashboard' },
    { id: 'route.iam.users', path: '/iam/users', title: 'Users Directory' },
    { id: 'route.iam.roles', path: '/iam/roles', title: 'Roles & Policies' },
    { id: 'route.iam.permissions', path: '/iam/permissions', title: 'Permissions Matrix' },
    { id: 'route.iam.sessions', path: '/iam/sessions', title: 'Active Sessions' },
    { id: 'route.iam.audit', path: '/iam/audit', title: 'Audit Logs' },
    { id: 'route.iam.tokens', path: '/iam/tokens', title: 'API Tokens' }
  ];

  routesList.forEach((r, idx) => {
    routes.register({
      id: r.id,
      module: IAM_MODULE_ID,
      path: r.path,
      // Default empty component builder for route matching
      component: () => null,
      title: r.title,
      order: idx + 1
    });
  });
};
export default registerRoutes;
