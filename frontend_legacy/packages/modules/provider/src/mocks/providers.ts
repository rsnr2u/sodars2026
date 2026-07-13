import { Provider } from '../types';
import { ProviderStatus } from '../enums';

export const mockProviders: Provider[] = [
  {
    id: 'prov_1',
    name: 'Metropolis Outdoor Media Corp',
    email: 'ops@metropolis-outdoor.com',
    phone: '+1-555-0911',
    status: ProviderStatus.Verified,
    primaryContact: { name: 'Alice Vance', email: 'alice.v@metropolis-outdoor.com', phone: '+1-555-0912', role: 'Operations Director' },
    agreements: [],
    documents: [],
    verifications: [],
    notes: [],
    timeline: [
      { id: 'time_1', type: 'provider.created', timestamp: Date.now() - 86400000 * 5, details: 'Metropolis Outdoor Media profile created.' }
    ],
    version: 1,
    isActive: true,
    createdAt: Date.now() - 86400000 * 5,
    updatedAt: Date.now() - 86400000 * 5
  },
  {
    id: 'prov_2',
    name: 'Apex Digital Screens Ltd',
    email: 'licensing@apex-screens.com',
    phone: '+1-555-0722',
    status: ProviderStatus.UnderReview,
    primaryContact: { name: 'Bob Gellar', email: 'bob.g@apex-screens.com', phone: '+1-555-0723', role: 'Business Dev Manager' },
    agreements: [],
    documents: [],
    verifications: [],
    notes: [],
    timeline: [
      { id: 'time_2', type: 'provider.created', timestamp: Date.now() - 86400000 * 2, details: 'Apex Digital Screens Ltd profile created.' }
    ],
    version: 1,
    isActive: true,
    createdAt: Date.now() - 86400000 * 2,
    updatedAt: Date.now() - 86400000 * 2
  }
];
export default mockProviders;
