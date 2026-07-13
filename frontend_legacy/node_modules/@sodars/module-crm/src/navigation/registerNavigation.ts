import { NavigationRegistry } from '@sodars/sdk';
import { CRM_MODULE_ID } from '../constants/module';

export const registerNavigation = (navigation: typeof NavigationRegistry): void => {
  navigation.register({
    id: 'crm',
    module: CRM_MODULE_ID,
    title: 'CRM Systems',
    icon: 'dashboard',
    order: 40,
  });

  navigation.register({
    id: 'crm.dashboard',
    module: CRM_MODULE_ID,
    title: 'CRM Overview',
    parent: 'crm',
    route: '/crm',
    order: 5,
  });

  navigation.register({
    id: 'crm.leads',
    module: CRM_MODULE_ID,
    title: 'Lead Management',
    parent: 'crm',
    order: 10,
  });

  navigation.register({
    id: 'crm.enquiries',
    module: CRM_MODULE_ID,
    title: 'Active Enquiries',
    parent: 'crm.leads',
    route: '/crm/enquiries',
    order: 5,
  });

  navigation.register({
    id: 'crm.quotations',
    module: CRM_MODULE_ID,
    title: 'Active Quotations',
    parent: 'crm.leads',
    route: '/crm/quotations',
    order: 10,
  });

  navigation.register({
    id: 'crm.customers_parent',
    module: CRM_MODULE_ID,
    title: 'Customer Management',
    parent: 'crm',
    order: 20,
  });

  navigation.register({
    id: 'crm.customers',
    module: CRM_MODULE_ID,
    title: 'Customers Directory',
    parent: 'crm.customers_parent',
    route: '/crm/customers',
    order: 5,
  });

  navigation.register({
    id: 'crm.productivity',
    module: CRM_MODULE_ID,
    title: 'Productivity Desk',
    parent: 'crm',
    order: 30,
  });

  navigation.register({
    id: 'crm.tasks',
    module: CRM_MODULE_ID,
    title: 'Productivity Tasks',
    parent: 'crm.productivity',
    route: '/crm/tasks',
    order: 5,
  });

  navigation.register({
    id: 'crm.followups',
    module: CRM_MODULE_ID,
    title: 'Follow Up Logs',
    parent: 'crm.productivity',
    route: '/crm/followups',
    order: 10,
  });

  navigation.register({
    id: 'crm.calendar',
    module: CRM_MODULE_ID,
    title: 'CRM Calendar',
    parent: 'crm.productivity',
    route: '/crm/calendar',
    order: 15,
  });

  navigation.register({
    id: 'crm.reports',
    module: CRM_MODULE_ID,
    title: 'Conversion Reports',
    parent: 'crm',
    route: '/crm/reports',
    order: 40,
  });
};
export default registerNavigation;
