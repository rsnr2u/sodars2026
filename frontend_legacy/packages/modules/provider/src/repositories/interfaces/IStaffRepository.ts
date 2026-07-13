import { BaseRepository } from '@sodars/api';
import { Staff } from '../../types';
import { StaffRole } from '../../enums';

export interface IStaffRepository extends BaseRepository<Staff> {
  findByProviderId(providerId: string): Promise<Staff[]>;
  findByBranchId(branchId: string): Promise<Staff[]>;
  findActive(): Promise<Staff[]>;
  findManagers(): Promise<Staff[]>;
  findByRole(role: StaffRole): Promise<Staff[]>;
  findByEmail(email: string): Promise<Staff | null>;
  exists(email: string): Promise<boolean>;
}
export default IStaffRepository;
