import { WidgetRegistry } from '@sodars/sdk';
import { PROVIDER_MODULE_ID } from '../constants/module';

export const registerWidgets = (widgets: typeof WidgetRegistry): void => {
  const widgetList = [
    // Operations Group
    { id: 'prov_total_providers', title: 'Total Providers', category: 'Operations' },
    { id: 'prov_verified_providers', title: 'Verified Providers', category: 'Operations' },
    { id: 'prov_pending_verification', title: 'Pending Verification', category: 'Operations' },
    { id: 'prov_branches', title: 'Branches Count', category: 'Operations' },
    { id: 'prov_staff', title: 'Staff Members', category: 'Operations' },

    // Compliance Group
    { id: 'prov_expired_agreements', title: 'Expired Agreements', category: 'Compliance' },
    { id: 'prov_expiring_documents', title: 'Expiring Documents', category: 'Compliance' },
    { id: 'prov_pending_approvals', title: 'Pending Approvals', category: 'Compliance' },
    { id: 'prov_docs_expiring_30_days', title: 'Documents Expiring (30 Days)', category: 'Compliance' },
    { id: 'prov_contracts_expiring', title: 'Contracts Expiring', category: 'Compliance' },

    // Business Group
    { id: 'prov_assigned_inventory', title: 'Assigned Inventory', category: 'Business' },
    { id: 'prov_unassigned_inventory', title: 'Unassigned Inventory', category: 'Business' },
    { id: 'prov_revenue', title: 'Revenue Contribution', category: 'Business' },
    { id: 'prov_avg_occupancy', title: 'Average Occupancy', category: 'Business' },

    // Health Group
    { id: 'prov_branches_offline', title: 'Branches Offline', category: 'Health' },
    { id: 'prov_inactive_staff', title: 'Inactive Staff', category: 'Health' },
    { id: 'prov_verification_queue', title: 'Verification Queue', category: 'Health' }
  ];

  widgetList.forEach((w, idx) => {
    widgets.register({
      id: w.id,
      module: PROVIDER_MODULE_ID,
      title: w.title,
      width: 1,
      height: 1,
      order: idx + 1,
      component: () => null
    });
  });
};
export default registerWidgets;
