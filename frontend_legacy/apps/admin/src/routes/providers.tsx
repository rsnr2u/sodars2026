import { Route as TSRoute } from '@tanstack/react-router';
import { Route as protectedRoute } from './_protected';
import { ProviderListPage } from '@sodars/module-provider';

export const Route = new TSRoute({
  getParentRoute: () => protectedRoute,
  path: '/providers',
  component: ProviderListPage,
});
