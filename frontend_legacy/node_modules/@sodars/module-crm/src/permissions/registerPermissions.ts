import { PermissionRegistry } from '@sodars/sdk';
import { CRM_MODULE_ID } from '../constants/module';

export const registerPermissions = (permissions: typeof PermissionRegistry): void => {
  const perms = [
    { id: 'crm.dashboard.view', resource: 'dashboard', action: 'view', category: 'CRM', description: 'Allows viewing general CRM metrics overview' },
    { id: 'crm.enquiries.view', resource: 'enquiries', action: 'view', category: 'CRM', description: 'Allows viewing leads and enquiries directories' },
    { id: 'crm.enquiries.create', resource: 'enquiries', action: 'create', category: 'CRM', description: 'Allows adding new enquiries' },
    { id: 'crm.enquiries.update', resource: 'enquiries', action: 'update', category: 'CRM', description: 'Allows updating details for enquiries' },
    { id: 'crm.enquiries.delete', resource: 'enquiries', action: 'delete', category: 'CRM', description: 'Allows removing enquiries' },
    { id: 'crm.enquiries.assign', resource: 'enquiries', action: 'assign', category: 'CRM', description: 'Allows routing leads assignment to team members' },
    { id: 'crm.customers.view', resource: 'customers', action: 'view', category: 'CRM', description: 'Allows reading company client records list' },
    { id: 'crm.customers.create', resource: 'customers', action: 'create', category: 'CRM', description: 'Allows adding customer profiles' },
    { id: 'crm.customers.update', resource: 'customers', action: 'update', category: 'CRM', description: 'Allows updating customer company profiles' },
    { id: 'crm.customers.delete', resource: 'customers', action: 'delete', category: 'CRM', description: 'Allows removing customer company profiles' },
    { id: 'crm.tasks.manage', resource: 'tasks', action: 'manage', category: 'CRM', description: 'Allows editing personal task lists' },
    { id: 'crm.followups.manage', resource: 'followups', action: 'manage', category: 'CRM', description: 'Allows modifying followup schedules' },
    { id: 'crm.reports.view', resource: 'reports', action: 'view', category: 'CRM', description: 'Allows viewing conversion analytics' }
  ];

  perms.forEach((p, idx) => {
    permissions.register({
      id: p.id,
      module: CRM_MODULE_ID,
      resource: p.resource,
      action: p.action,
      category: p.category,
      description: p.description,
      order: idx + 1
    });
  });
};
export default registerPermissions;
