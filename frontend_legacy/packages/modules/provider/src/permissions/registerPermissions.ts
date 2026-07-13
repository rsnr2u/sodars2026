import { PermissionRegistry } from '@sodars/sdk';
import { PROVIDER_MODULE_ID } from '../constants/module';

export const registerPermissions = (permissions: typeof PermissionRegistry): void => {
  const perms = [
    { id: 'provider.dashboard.view', resource: 'dashboard', action: 'view', category: 'Provider', description: 'Allows viewing provider analytics metrics dashboard' },
    { id: 'provider.providers.view', resource: 'providers', action: 'view', category: 'Provider', description: 'Allows viewing provider directory lists' },
    { id: 'provider.providers.create', resource: 'providers', action: 'create', category: 'Provider', description: 'Allows adding new provider records' },
    { id: 'provider.providers.update', resource: 'providers', action: 'update', category: 'Provider', description: 'Allows editing provider details' },
    { id: 'provider.providers.delete', resource: 'providers', action: 'delete', category: 'Provider', description: 'Allows soft-deleting provider accounts' },
    { id: 'provider.branches.view', resource: 'branches', action: 'view', category: 'Provider', description: 'Allows viewing branches profile records' },
    { id: 'provider.branches.create', resource: 'branches', action: 'create', category: 'Provider', description: 'Allows registering branch coordinates' },
    { id: 'provider.branches.update', resource: 'branches', action: 'update', category: 'Provider', description: 'Allows updating branch details' },
    { id: 'provider.branches.delete', resource: 'branches', action: 'delete', category: 'Provider', description: 'Allows removing branch profiles' },
    { id: 'provider.staff.view', resource: 'staff', action: 'view', category: 'Provider', description: 'Allows viewing staff lookup directory' },
    { id: 'provider.staff.create', resource: 'staff', action: 'create', category: 'Provider', description: 'Allows adding staff members' },
    { id: 'provider.staff.update', resource: 'staff', action: 'update', category: 'Provider', description: 'Allows updating staff record details' },
    { id: 'provider.staff.delete', resource: 'staff', action: 'delete', category: 'Provider', description: 'Allows deleting staff accounts' },
    { id: 'provider.documents.view', resource: 'documents', action: 'view', category: 'Provider', description: 'Allows viewing compliance documents files list' },
    { id: 'provider.documents.manage', resource: 'documents', action: 'manage', category: 'Provider', description: 'Allows uploading or deleting compliance attachments' },
    { id: 'provider.agreements.view', resource: 'agreements', action: 'view', category: 'Provider', description: 'Allows viewing agreements contracts metadata' },
    { id: 'provider.agreements.manage', resource: 'agreements', action: 'manage', category: 'Provider', description: 'Allows uploading or signing legal agreements' },
    { id: 'provider.verification.view', resource: 'verification', action: 'view', category: 'Provider', description: 'Allows viewing verification workflows list queue' },
    { id: 'provider.verification.approve', resource: 'verification', action: 'approve', category: 'Provider', description: 'Allows approving verification status' },
    { id: 'provider.verification.reject', resource: 'verification', action: 'reject', category: 'Provider', description: 'Allows rejecting verification audits' },
    { id: 'provider.settings.manage', resource: 'settings', action: 'manage', category: 'Provider', description: 'Allows managing provider config preferences' },
    { id: 'provider.timeline.view', resource: 'timeline', action: 'view', category: 'Provider', description: 'Allows viewing audit timeline feeds' },
    { id: 'provider.bankaccounts.manage', resource: 'bankaccounts', action: 'manage', category: 'Provider', description: 'Allows updating bank coordinates' },
    { id: 'provider.reports.view', resource: 'reports', action: 'view', category: 'Provider', description: 'Allows viewing provider metrics summaries' }
  ];

  perms.forEach((p, idx) => {
    permissions.register({
      id: p.id,
      module: PROVIDER_MODULE_ID,
      resource: p.resource,
      action: p.action,
      category: p.category,
      description: p.description,
      order: idx + 1
    });
  });
};
export default registerPermissions;
