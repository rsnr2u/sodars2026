import { create } from 'zustand';
import { OrganizationDTO } from '@sodars/contracts';

export interface BranchDTO {
  id: string;
  name: string;
  code: string;
}

export interface WorkspaceDTO {
  id: string;
  name: string;
}

export interface TenantState {
  activeOrganization: OrganizationDTO | null;
  activeBranch: BranchDTO | null;
  activeWorkspace: WorkspaceDTO | null;
  activeRole: string | null;
  setActiveOrganization: (org: OrganizationDTO | null) => void;
  setActiveBranch: (branch: BranchDTO | null) => void;
  setActiveWorkspace: (workspace: WorkspaceDTO | null) => void;
  setActiveRole: (role: string | null) => void;
}

export const useTenantStore = create<TenantState>((set) => ({
  activeOrganization: null,
  activeBranch: null,
  activeWorkspace: null,
  activeRole: null,
  setActiveOrganization: (org) => set({ activeOrganization: org }),
  setActiveBranch: (branch) => set({ activeBranch: branch }),
  setActiveWorkspace: (workspace) => set({ activeWorkspace: workspace }),
  setActiveRole: (role) => set({ activeRole: role }),
}));
