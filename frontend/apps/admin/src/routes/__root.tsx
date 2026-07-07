import React from 'react';
import { createRootRoute, Outlet } from '@tanstack/react-router';
import { ShellLayout } from '@sodars/layout';

export const Route = createRootRoute({
  component: RootComponent,
});

function RootComponent() {
  return React.createElement(
    ShellLayout,
    null,
    React.createElement(Outlet)
  );
}
