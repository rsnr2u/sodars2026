import { providerRepositories } from '../repositories';
import { StaffSchema } from '../schemas';
import { StaffTelemetry } from '../telemetry';
import { StaffStatus } from '../enums';
import type { Staff } from '../types';

export class StaffService {
  private static readonly repository = providerRepositories.staff;

  public static async getStaff(providerId?: string): Promise<Staff[]> {
    const staffList = providerId
      ? await this.repository.findByProviderId(providerId)
      : await this.repository.findAll();
    return StaffSchema.validateMany(staffList);
  }

  public static async getBranchStaff(branchId: string): Promise<Staff[]> {
    const staffList = await this.repository.findByBranchId(branchId);
    return StaffSchema.validateMany(staffList);
  }

  public static async getStaffMember(id: string): Promise<Staff | null> {
    const staff = await this.repository.findById(id);
    return staff ? StaffSchema.validate(staff) : null;
  }

  public static async createStaff(staff: Staff): Promise<Staff> {
    const validated = StaffSchema.validate(staff);
    const created = await this.repository.create(validated);
    StaffTelemetry.trackCreated(created.providerId, created.id);
    return created;
  }

  public static async updateStaff(
    id: string,
    changes: Partial<Staff>
  ): Promise<Staff> {
    const updated = await this.repository.update(id, changes);
    StaffTelemetry.trackUpdated(updated.providerId, id);
    return StaffSchema.validate(updated);
  }

  public static async deleteStaff(id: string): Promise<void> {
    const staff = await this.repository.findById(id);
    if (staff) {
      await this.repository.delete(id);
      StaffTelemetry.trackDeleted(staff.providerId, id);
    }
  }

  public static async getActiveStaff(): Promise<Staff[]> {
    const staffList = await this.repository.findActive();
    return StaffSchema.validateMany(staffList);
  }

  public static async getManagers(): Promise<Staff[]> {
    const staffList = await this.repository.findManagers();
    return StaffSchema.validateMany(staffList);
  }

  public static async getStaffByEmail(
    email: string
  ): Promise<Staff | null> {
    const staff = await this.repository.findByEmail(email);
    return staff ? StaffSchema.validate(staff) : null;
  }

  public static async exists(email: string): Promise<boolean> {
    return this.repository.exists(email);
  }

  public static async assignToBranch(
    staffId: string,
    branchId: string
  ): Promise<Staff> {
    const updated = await this.repository.update(staffId, { branchId });
    StaffTelemetry.trackAssigned(updated.providerId, staffId, branchId);
    return StaffSchema.validate(updated);
  }

  public static async transferBranch(
    staffId: string,
    newBranchId: string
  ): Promise<Staff> {
    const updated = await this.repository.update(staffId, { branchId: newBranchId });
    StaffTelemetry.trackTransferred(updated.providerId, staffId, newBranchId);
    return StaffSchema.validate(updated);
  }

  public static async activate(staffId: string): Promise<void> {
    const updated = await this.repository.update(staffId, { status: StaffStatus.Active });
    StaffTelemetry.trackActivated(updated.providerId, staffId);
  }

  public static async deactivate(staffId: string): Promise<void> {
    const updated = await this.repository.update(staffId, { status: StaffStatus.Inactive });
    StaffTelemetry.trackDeactivated(updated.providerId, staffId);
  }
}
export default StaffService;
