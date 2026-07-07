import React from 'react';
import ReactDOM from 'react-dom/client';
import { RouterProvider, createRouter } from '@tanstack/react-router';
import { NavigationRegistry } from '@sodars/sdk';
import { useAuthStore, useTenantStore } from '@sodars/store';
import { AppProviders } from '@sodars/providers';
import { mockUser, mockOrganization } from '@sodars/testing';
import './index.css';

// 1. Register fallback routes in Navigation SDK
NavigationRegistry.register({
  id: 'dashboard',
  label: 'Dashboard Control',
  path: '/',
  icon: 'LayoutDashboard',
  priority: 1,
});

NavigationRegistry.register({
  id: 'crm',
  label: 'CRM Leads',
  path: '/crm',
  icon: 'Users',
  priority: 10,
});

NavigationRegistry.register({
  id: 'campaigns',
  label: 'Campaigns Control',
  path: '/campaigns',
  icon: 'Megaphone',
  priority: 20,
});

NavigationRegistry.register({
  id: 'operations',
  label: 'Operations Planner',
  path: '/operations',
  icon: 'CalendarDays',
  priority: 30,
});

// Import route definitions
import { Route as rootRoute } from './routes/__root';
import { Route as indexRoute } from './routes/index';
import { Route as loginRoute } from './routes/login';
import { Route as protectedRoute } from './routes/_protected';

// Build route tree mapping
const routeTree = rootRoute.addChildren([
  loginRoute as any,
  protectedRoute.addChildren([
    indexRoute as any,
  ]) as any,
]);

// Build router instance
const router = createRouter({ routeTree });

// Inject local mock credentials for testing/initial UI loading
const initAuth = () => {
  const authStore = useAuthStore.getState();
  if (!authStore.token) {
    authStore.setSession('mock-jwt-auth-session-key', 'mock-refresh-token', mockUser);
  }
  const tenantStore = useTenantStore.getState();
  if (!tenantStore.activeOrganization) {
    tenantStore.setActiveOrganization(mockOrganization);
  }
};

initAuth();

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <AppProviders>
      <RouterProvider router={router} />
    </AppProviders>
  </React.StrictMode>
);
