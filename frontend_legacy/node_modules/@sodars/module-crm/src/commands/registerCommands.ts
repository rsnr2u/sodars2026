import { CommandRegistry } from '@sodars/sdk';
import { CRM_MODULE_ID } from '../constants/module';

export const registerCommands = (commands: typeof CommandRegistry): void => {
  const commandsList = [
    { id: 'cmd.crm.dashboard', title: 'Open CRM Dashboard', route: '/crm', keywords: ['crm', 'dashboard', 'pipeline overview'] },
    { id: 'cmd.crm.enquiry.create', title: 'Create Enquiry Lead', route: '/crm/enquiries/new', keywords: ['create enquiry', 'add lead', 'new enquiry'] },
    { id: 'cmd.crm.enquiry.assign', title: 'Assign Lead Enquiry', route: '/crm/enquiries', keywords: ['assign lead', 'assign enquiry', 'route lead'] },
    { id: 'cmd.crm.customer.create', title: 'Create Customer Account', route: '/crm/customers/new', keywords: ['create customer', 'add client', 'new customer'] },
    { id: 'cmd.crm.customer.convert', title: 'Convert Customer Profile', route: '/crm/enquiries', keywords: ['convert lead', 'convert client', 'win opportunity'] },
    { id: 'cmd.crm.quotation.create', title: 'Create Quotation Proposal', route: '/crm/quotations/new', keywords: ['create quote', 'generate proposal', 'price estimate'] },
    { id: 'cmd.crm.followup.create', title: 'Add Follow-up Callback', route: '/crm/followups', keywords: ['add followup', 'schedule call', 'callback logs'] },
    { id: 'cmd.crm.task.create', title: 'Create Productivity Task', route: '/crm/tasks', keywords: ['create task', 'add to-do', 'productivity checklist'] },
    { id: 'cmd.crm.customer.search', title: 'Search Customers List', route: '/crm/customers', keywords: ['search customer', 'find client', 'lookup contact'] },
    { id: 'cmd.crm.lead.search', title: 'Search Leads / Enquiries', route: '/crm/enquiries', keywords: ['search lead', 'search enquiry', 'find lead'] }
  ];

  for (const cmd of commandsList) {
    commands.register({
      id: cmd.id,
      module: CRM_MODULE_ID,
      title: cmd.title,
      keywords: cmd.keywords,
      group: 'Customer Relations (CRM)',
      order: 20,
      execute: (ctx) => {
        ctx.router.navigate(cmd.route);
      }
    });
  }
};
export default registerCommands;
