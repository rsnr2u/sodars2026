import { create } from 'zustand';

export type SessionState =
  | 'Unauthenticated'
  | 'Authenticating'
  | 'Authenticated'
  | 'Refreshing'
  | 'Expired'
  | 'LoggedOut';

export interface SessionStoreState {
  state: SessionState;
  setState: (state: SessionState) => void;
}

export const useSessionStore = create<SessionStoreState>((set) => ({
  state: 'Unauthenticated',
  setState: (state) => set({ state }),
}));
