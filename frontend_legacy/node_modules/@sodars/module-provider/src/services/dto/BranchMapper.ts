import type { Branch } from '../../types';

export class BranchMapper {
  public static toDomain(dto: any): Branch {
    return {
      id: dto.id,
      providerId: dto.providerId,
      name: dto.name,
      address: dto.address,
      phone: dto.phone,
      email: dto.email,
      isMainBranch: dto.isMainBranch,
      status: dto.status,
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

  public static toDTO(domain: Branch): any {
    return { ...domain };
  }
}
export default BranchMapper;
