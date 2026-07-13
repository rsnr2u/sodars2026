import React from 'react';
import { createRoot } from 'react-dom/client';
import { AppShell } from '@sodars/design-system';
import { ProvidersDirectory, ProviderModuleManifest } from '@sodars/modules';
import { ModuleRegistry, PermissionRegistry, NotificationRegistry } from '@sodars/core';
import './styles/index.css';

// 1. Bootstrap SODAARS Module and Permission Registries
ModuleRegistry.register(ProviderModuleManifest);
PermissionRegistry.setPermissions(['providers:view', 'providers:verify']);

// 2. Add realistic Indian media ERP notification alerts to the core registry
NotificationRegistry.add(
  'Compliance Check Failed',
  'Bright Outdoor Media (Mumbai) lease agreement expired.'
);
NotificationRegistry.add(
  'Verification Action Required',
  'Apex Ads GST validation has been marked pending verification.'
);

// 3. Mount and Render SODAARS client AppShell Layout frame wrapper
const container = document.getElementById('root');
if (container) {
  const root = createRoot(container);
  root.render(
    <React.StrictMode>
      <AppShell>
        <ProvidersDirectory />
      </AppShell>
    </React.StrictMode>
  );
}
