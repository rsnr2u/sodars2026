import { MockRepository } from '@sodars/api';
import { StaffRole, StaffStatus } from '../enums';
import { mockStaff } from '../mocks';
import type { Staff } from '../types';
import type { IStaffRepository } from './interfaces';

const MANAGER_ROLES: ReadonlySet<StaffRole> = new Set([
  StaffRole.BranchManager,
  StaffRole.SalesManager,
  StaffRole.OperationsManager,
  StaffRole.InventoryManager,
  StaffRole.FinanceManager,
]);

export class MockStaffRepository
  extends MockRepository<Staff>
  implements IStaffRepository
{
  constructor() {
    super(mockStaff);
  }

  async findByProviderId(providerId: string): Promise<Staff[]> {
    return this.filter(staff => staff.providerId === providerId);
  }

  async findByBranchId(branchId: string): Promise<Staff[]> {
    return this.filter(staff => staff.branchId === branchId);
  }

  async findActive(): Promise<Staff[]> {
    return this.filter(staff => staff.status === StaffStatus.Active);
  }

  async findManagers(): Promise<Staff[]> {
    return this.filter(staff => MANAGER_ROLES.has(staff.designation));
  }

  async findByRole(role: StaffRole): Promise<Staff[]> {
    return this.filter(staff => staff.designation === role);
  }

  async findByEmail(email: string): Promise<Staff | null> {
    const normalized = email.trim().toLowerCase();

    return (
      this.first(
        staff => staff.email.value.trim().toLowerCase() === normalized,
      ) ?? null
    );
  }

  async exists(email: string): Promise<boolean> {
    return (await this.findByEmail(email)) !== null;
  }
}
export default MockStaffRepository;
