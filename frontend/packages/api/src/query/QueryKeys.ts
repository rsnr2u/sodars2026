export const QueryKeys = {
  iam: {
    users: ['iam', 'users'],
    user: (id: string) => ['iam', 'users', id],
    roles: ['iam', 'roles'],
    permissions: ['iam', 'permissions'],
  },
  crm: {
    leads: ['crm', 'leads'],
    enquiries: ['crm', 'enquiries'],
  },
  inventory: {
    hoardings: ['inventory', 'hoardings'],
    providers: ['inventory', 'providers'],
  },
  booking: {
    campaigns: ['booking', 'campaigns'],
    bookings: ['booking', 'bookings'],
  }
} as const;

export type QueryKeyType = typeof QueryKeys;
export default QueryKeys;
