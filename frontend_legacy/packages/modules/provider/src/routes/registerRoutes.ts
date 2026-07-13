import { RouteRegistry } from '@sodars/sdk';
import { PROVIDER_MODULE_ID } from '../constants/module';

export const registerRoutes = (routes: typeof RouteRegistry): void => {
  const routesList = [
    { id: 'route.provider.dashboard', path: '/provider', title: 'Provider Analytics Dashboard' },
    { id: 'route.provider.providers', path: '/providers', title: 'Providers Directory List' },
    { id: 'route.provider.providers.new', path: '/providers/new', title: 'Register New Provider Profile' },
    { id: 'route.provider.providers.detail', path: '/providers/:id', title: 'Provider Aggregate Detail View' },
    { id: 'route.provider.providers.edit', path: '/providers/:id/edit', title: 'Edit Provider Panel Settings' },
    { id: 'route.provider.providers.branches', path: '/providers/:id/branches', title: 'Provider Branches Directory' },
    { id: 'route.provider.providers.staff', path: '/providers/:id/staff', title: 'Provider Staff Directory' },
    { id: 'route.provider.providers.documents', path: '/providers/:id/documents', title: 'Provider Documents Safe' },
    { id: 'route.provider.providers.agreements', path: '/providers/:id/agreements', title: 'Provider Agreements Manager' },
    { id: 'route.provider.providers.bank_accounts', path: '/providers/:id/bank-accounts', title: 'Provider Bank Coordinate Settings' },
    { id: 'route.provider.providers.verification', path: '/providers/:id/verification', title: 'Provider Verification Workflows Panel' },
    { id: 'route.provider.providers.timeline', path: '/providers/:id/timeline', title: 'Provider Historical Event Timeline' },
    { id: 'route.provider.providers.settings', path: '/providers/:id/settings', title: 'Provider Preference Details' },
    { id: 'route.provider.reports', path: '/providers/reports', title: 'Compliance Audit Reports' }
  ];

  routesList.forEach((r, idx) => {
    routes.register({
      id: r.id,
      module: PROVIDER_MODULE_ID,
      path: r.path,
      component: () => null,
      title: r.title,
      order: idx + 1
    });
  });
};
export default registerRoutes;
