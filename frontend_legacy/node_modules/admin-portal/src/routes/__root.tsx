import React from 'react';
import { createRootRoute, Outlet, useLocation } from '@tanstack/react-router';
import { AdminLayout, AuthLayout } from '@sodars/layout';

export const Route = createRootRoute({
  component: RootComponent,
});

function RootComponent() {
  const location = useLocation();
  const isAuthPage = location.pathname === '/login';

  return React.createElement(
    isAuthPage ? AuthLayout : AdminLayout,
    null,
    React.createElement(Outlet)
  );
}
