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
  module: 'dashboard',
  title: 'Dashboard Control',
  route: '/',
  icon: 'dashboard',
  order: 1,
});

NavigationRegistry.register({
  id: 'crm',
  module: 'crm',
  title: 'CRM Leads',
  route: '/crm',
  icon: 'crm',
  order: 10,
});

NavigationRegistry.register({
  id: 'campaigns',
  module: 'campaign',
  title: 'Campaigns Control',
  route: '/campaigns',
  icon: 'campaign',
  order: 20,
});

NavigationRegistry.register({
  id: 'operations',
  module: 'operations',
  title: 'Operations Planner',
  route: '/operations',
  icon: 'operations',
  order: 30,
});

NavigationRegistry.register({
  id: 'diagnostics',
  module: 'settings',
  title: 'Diagnostics Console',
  route: '/diagnostics',
  icon: 'settings',
  order: 100,
});

// Import route definitions
import { Route as rootRoute } from './routes/__root';
import { Route as indexRoute } from './routes/index';
import { Route as loginRoute } from './routes/login';
import { Route as protectedRoute } from './routes/_protected';
import { Route as diagnosticsRoute } from './routes/diagnostics';
import { Route as iamUsersRoute } from './routes/iam.users';
import { Route as iamRolesRoute } from './routes/iam.roles';

// Build route tree mapping
const routeTree = rootRoute.addChildren([
  loginRoute as any,
  protectedRoute.addChildren([
    indexRoute as any,
    diagnosticsRoute as any,
    iamUsersRoute as any,
    iamRolesRoute as any,
  ]) as any,
]);

// Build router instance
const router = createRouter({ routeTree });

import { CommandRegistry } from '@sodars/sdk';

CommandRegistry.register({
  id: 'cmd.diagnostics',
  module: 'settings',
  title: 'Open Diagnostics Console',
  keywords: ['diagnostics', 'logs', 'telemetry', 'debug'],
  description: 'Navigate to development systems diagnostics console dashboard',
  group: 'System',
  icon: 'settings',
  order: 1,
  execute: (ctx) => {
    ctx.router.navigate('/diagnostics');
  }
});

CommandRegistry.register({
  id: 'cmd.dashboard',
  module: 'dashboard',
  title: 'Go to Dashboard Overview',
  keywords: ['home', 'dashboard', 'control room'],
  description: 'Navigate to platform status control overview',
  group: 'Navigation',
  icon: 'dashboard',
  order: 2,
  execute: (ctx) => {
    ctx.router.navigate('/');
  }
});

import { Config } from '@sodars/config';
import { EventBus } from '@sodars/events';
import { identity } from '@sodars/auth';
import { Telemetry } from '@sodars/observability';
import { 
  ModuleManager, 
  registryManager, 
  BootstrapContext 
} from '@sodars/sdk';
import { IamModule } from '@sodars/module-iam';

const bootstrapContext: BootstrapContext = {
  config: Config,
  eventBus: EventBus,
  queryClient: {
    getQueryData: <T,>(_key: string[]): T | undefined => undefined,
    setQueryData: <T,>(_key: string[], _data: T): void => {}
  },
  identity: identity,
  registry: registryManager,
  requestContext: {
    getHeaders: () => ({
      'X-Locale': navigator.language || 'en-US',
      'X-Timezone': Intl.DateTimeFormat().resolvedOptions().timeZone
    })
  },
  telemetry: {
    trackEvent: (name: string, props?: Record<string, unknown>): void => {
      Telemetry.track(name as any, undefined, props);
    },
    trackError: (error: Error, _severity?: string): void => {
      Telemetry.trackError(error);
    }
  }
};

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

// Install IAM Reference Module dynamically
ModuleManager.install(new IamModule(), bootstrapContext)
  .then(() => {
    console.log('[App] IamModule installed successfully!');
  })
  .catch(err => {
    console.error('[App] Failed to install IamModule:', err);
  });

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <AppProviders>
      <RouterProvider router={router} />
    </AppProviders>
  </React.StrictMode>
);
