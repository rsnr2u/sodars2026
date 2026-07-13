import type { Provider } from '../../types';

export class ProviderMapper {
  public static toDomain(dto: any): Provider {
    return {
      id: dto.id,
      name: dto.name,
      email: dto.email,
      phone: dto.phone,
      status: dto.status,
      rejectionReason: dto.rejectionReason,
      primaryContact: dto.primaryContact,
      gstRegistration: dto.gstRegistration,
      bankAccount: dto.bankAccount,
      agreements: dto.agreements || [],
      documents: dto.documents || [],
      verifications: dto.verifications || [],
      notes: dto.notes || [],
      timeline: dto.timeline || [],
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

  public static toDTO(domain: Provider): any {
    return { ...domain };
  }
}
export default ProviderMapper;
