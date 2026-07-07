import { create } from 'zustand';
import { UserDTO, OrganizationDTO } from '@sodars/contracts';

interface AuthState {
  token: string | null;
  user: UserDTO | null;
  setSession: (token: string, user: UserDTO) => void;
  clearSession: () => void;
  hasPermission: (permission: string) => boolean;
}

export const useAuthStore = create<AuthState>((set, get) => ({
  token: null,
  user: null,
  setSession: (token, user) => set({ token, user }),
  clearSession: () => set({ token: null, user: null }),
  hasPermission: (permission) => {
    const user = get().user;
    if (!user) return false;
    if (user.roles.includes('super_admin')) return true;
    return user.permissions.includes(permission);
  }
}));

interface TenantState {
  activeOrganization: OrganizationDTO | null;
  setActiveOrganization: (org: OrganizationDTO | null) => void;
}

export const useTenantStore = create<TenantState>((set) => ({
  activeOrganization: null,
  setActiveOrganization: (org) => set({ activeOrganization: org }),
}));
