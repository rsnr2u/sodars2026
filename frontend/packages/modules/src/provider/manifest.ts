import { ModuleManifest } from '@sodars/core';

export const ProviderModuleManifest: ModuleManifest = {
  id: 'provider-module',
  name: 'Providers Workspace',
  routes: [
    { path: '/providers/directory', component: null }
  ],
  navigation: [
    { label: 'Providers Directory', path: '/providers/directory', permission: 'providers:view' }
  ],
  permissions: ['providers:view', 'providers:verify'],
  commands: [
    { phrase: 'View Providers Directory', action: () => { window.location.hash = '/providers/directory'; } }
  ]
};
