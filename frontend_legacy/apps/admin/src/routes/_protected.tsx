import React from 'react';
import { Route as TSRoute, Outlet } from '@tanstack/react-router';
import { Route as rootRoute } from './__root';
import { identity } from '@sodars/auth';

export const Route = new TSRoute({
  getParentRoute: () => rootRoute,
  id: 'protected',
  beforeLoad: () => {
    if (!identity.isAuthenticated()) {
      window.location.href = '/login';
    }
  },
  component: () => React.createElement(Outlet),
});
export default Route;
