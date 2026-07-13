import type { Staff } from '../../types';

export class StaffMapper {
  public static toDomain(dto: any): Staff {
    return {
      id: dto.id,
      providerId: dto.providerId,
      branchId: dto.branchId,
      employeeCode: dto.employeeCode,
      name: dto.name,
      email: dto.email,
      phone: dto.phone,
      designation: dto.designation,
      status: dto.status,
      reportingTo: dto.reportingTo,
      joiningDate: dto.joiningDate,
      tenantId: dto.tenantId,
      createdAt: dto.createdAt,
      updatedAt: dto.updatedAt,
      createdBy: dto.createdBy,
      updatedBy: dto.updatedBy,
      deletedAt: dto.deletedAt,
      deletedBy: dto.deletedBy,
      isActive: dto.isActive ?? true,
      version: dto.version || 1,
    };
  }

  public static toDTO(domain: Staff): any {
    return { ...domain };
  }
}
export default StaffMapper;
