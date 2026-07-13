import { Customer } from '../types';

export const mockCustomers: Customer[] = [
  {
    id: 'cust_1',
    companyName: 'Acme Corporation',
    email: 'billing@acme.com',
    phone: '+1-555-9000',
    contacts: [
      { name: 'John Doe', email: 'john.doe@acme.com', phone: '+1-555-9001', role: 'Billing Manager' }
    ],
    addresses: [
      { street: '123 Industrial Rd', city: 'Metropolis', state: 'NY', zipCode: '10001', country: 'US', isBilling: true }
    ],
    timeline: [
      { id: 't_1', type: 'Customer Created', timestamp: Date.now() - 86400000 * 20, details: 'Acme Corporation profile registered.' }
    ],
    createdAt: Date.now() - 86400000 * 20
  },
  {
    id: 'cust_2',
    companyName: 'Stark Industries LLC',
    email: 'accounting@stark.com',
    phone: '+1-555-8000',
    contacts: [
      { name: 'Pepper Potts', email: 'pepper@stark.com', phone: '+1-555-8001', role: 'CEO Office Coordinator' }
    ],
    addresses: [
      { street: '456 Stark Tower Blvd', city: 'Los Angeles', state: 'CA', zipCode: '90001', country: 'US', isBilling: true }
    ],
    timeline: [
      { id: 't_2', type: 'Customer Created', timestamp: Date.now() - 86400000 * 30, details: 'Stark Industries LLC profile registered.' }
    ],
    createdAt: Date.now() - 86400000 * 30
  }
];
export default mockCustomers;
