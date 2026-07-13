export interface EntityBase {
  id: string;
  tenantId?: string;
  createdAt: number;
  updatedAt: number;
  createdBy?: string;
  updatedBy?: string;
  deletedAt?: number;
  deletedBy?: string;
  isActive: boolean;
  version: number;
}
