import { useAuthStore, useTenantStore, usePermissionStore } from '@sodars/store';
import { UserDTO, OrganizationDTO } from '@sodars/contracts';
import { BranchDTO } from '@sodars/store';

export class IdentityFacade {
  public user(): UserDTO | null {
    return useAuthStore.getState().user;
  }

  public organization(): OrganizationDTO | null {
    return useTenantStore.getState().activeOrganization;
  }

  public branch(): BranchDTO | null {
    return useTenantStore.getState().activeBranch;
  }

  public permissions(): string[] {
    return usePermissionStore.getState().permissions;
  }

  public roles(): string[] {
    return usePermissionStore.getState().roles;
  }

  public isAuthenticated(): boolean {
    return useAuthStore.getState().token !== null;
  }

  // Permission Policies Gates matching Laravel Blade syntax
  public can(permission: string): boolean {
    const roles = this.roles();
    if (roles.includes('super_admin')) return true;
    return this.permissions().includes(permission);
  }

  public canAny(permissions: string[]): boolean {
    const roles = this.roles();
    if (roles.includes('super_admin')) return true;
    const userPerms = this.permissions();
    return permissions.some(p => userPerms.includes(p));
  }

  public canAll(permissions: string[]): boolean {
    const roles = this.roles();
    if (roles.includes('super_admin')) return true;
    const userPerms = this.permissions();
    return permissions.every(p => userPerms.includes(p));
  }
}

export const identity = new IdentityFacade();
export default identity;
