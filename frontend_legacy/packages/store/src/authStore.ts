import { create } from 'zustand';
import { UserDTO } from '@sodars/contracts';

export interface AuthState {
  token: string | null;
  refreshToken: string | null;
  user: UserDTO | null;
  setSession: (token: string, refreshToken: string, user: UserDTO) => void;
  clearSession: () => void;
}

export const useAuthStore = create<AuthState>((set) => ({
  token: null,
  refreshToken: null,
  user: null,
  setSession: (token, refreshToken, user) => set({ token, refreshToken, user }),
  clearSession: () => set({ token: null, refreshToken: null, user: null }),
}));
