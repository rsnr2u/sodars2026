import { StaffRole } from '../enums';

export const MANAGER_ROLES = new Set<StaffRole>([
  StaffRole.BranchManager,
  StaffRole.SalesManager,
  StaffRole.OperationsManager,
  StaffRole.InventoryManager,
  StaffRole.FinanceManager,
]);
