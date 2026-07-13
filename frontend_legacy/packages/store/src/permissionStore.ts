import { create } from 'zustand';

export interface PermissionState {
  permissions: string[];
  roles: string[];
  setPermissions: (permissions: string[], roles: string[]) => void;
  clearPermissions: () => void;
}

export const usePermissionStore = create<PermissionState>((set) => ({
  permissions: [],
  roles: [],
  setPermissions: (permissions, roles) => set({ permissions, roles }),
  clearPermissions: () => set({ permissions: [], roles: [] }),
}));
