import { RouteRegistry } from '@sodars/sdk';
import { CRM_MODULE_ID } from '../constants/module';

export const registerRoutes = (routes: typeof RouteRegistry): void => {
  const routesList = [
    { id: 'route.crm.dashboard', path: '/crm', title: 'CRM Overview' },
    { id: 'route.crm.enquiries', path: '/crm/enquiries', title: 'Active Enquiries' },
    { id: 'route.crm.enquiries.new', path: '/crm/enquiries/new', title: 'Create Enquiry Lead' },
    { id: 'route.crm.enquiries.detail', path: '/crm/enquiries/:id', title: 'Lead Profiler Details' },
    { id: 'route.crm.customers', path: '/crm/customers', title: 'Customers Directory' },
    { id: 'route.crm.customers.new', path: '/crm/customers/new', title: 'Create Customer Account' },
    { id: 'route.crm.customers.detail', path: '/crm/customers/:id', title: 'Customer Profile Details' },
    { id: 'route.crm.quotations', path: '/crm/quotations', title: 'Quotations Proposal' },
    { id: 'route.crm.quotations.new', path: '/crm/quotations/new', title: 'Create Quotation Proposal' },
    { id: 'route.crm.quotations.detail', path: '/crm/quotations/:id', title: 'Quotation Details' },
    { id: 'route.crm.tasks', path: '/crm/tasks', title: 'Productivity Tasks' },
    { id: 'route.crm.followups', path: '/crm/followups', title: 'Follow Up Logs' },
    { id: 'route.crm.calendar', path: '/crm/calendar', title: 'CRM Calendar' },
    { id: 'route.crm.reports', path: '/crm/reports', title: 'Conversion Reports' }
  ];

  routesList.forEach((r, idx) => {
    routes.register({
      id: r.id,
      module: CRM_MODULE_ID,
      path: r.path,
      // Default empty component builder for route matching
      component: () => null,
      title: r.title,
      order: idx + 1
    });
  });
};
export default registerRoutes;
