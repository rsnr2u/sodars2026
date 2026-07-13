import { BranchStatus } from '../enums';
import { Branch } from '../types';

export const mockBranches: Branch[] = [
  {
    id: 'branch-1',
    providerId: 'prov_1',
    name: 'Guntur Branch',
    address: { street: 'Main Bazar Rd', city: 'Guntur', state: 'AP', zipCode: '522003', country: 'IN', isBilling: true },
    phone: { countryCode: '+91', number: '9876543210' },
    email: { value: 'guntur@metropolis-outdoor.com' },
    isMainBranch: true,
    status: BranchStatus.Active,
    version: 1,
    isActive: true,
    createdAt: Date.now(),
    updatedAt: Date.now()
  },
  {
    id: 'branch-2',
    providerId: 'prov_1',
    name: 'Vijayawada Branch',
    address: { street: 'Benz Circle', city: 'Vijayawada', state: 'AP', zipCode: '520010', country: 'IN', isBilling: false },
    phone: { countryCode: '+91', number: '9876543211' },
    email: { value: 'vijayawada@metropolis-outdoor.com' },
    isMainBranch: false,
    status: BranchStatus.Offline,
    version: 1,
    isActive: true,
    createdAt: Date.now(),
    updatedAt: Date.now()
  }
];
export default mockBranches;
