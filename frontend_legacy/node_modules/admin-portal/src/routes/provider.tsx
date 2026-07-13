import { Route as TSRoute } from '@tanstack/react-router';
import { Route as protectedRoute } from './_protected';
import { DashboardPage } from '@sodars/module-provider';

export const Route = new TSRoute({
  getParentRoute: () => protectedRoute,
  path: '/provider',
  component: DashboardPage,
});
