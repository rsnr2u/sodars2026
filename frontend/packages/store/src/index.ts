import { create } from 'zustand';
import { UserDTO, OrganizationDTO } from '@sodars/contracts';

// 1. Auth Store
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

// 2. Tenant Store
interface TenantState {
  activeOrganization: OrganizationDTO | null;
  setActiveOrganization: (org: OrganizationDTO | null) => void;
}

export const useTenantStore = create<TenantState>((set) => ({
  activeOrganization: null,
  setActiveOrganization: (org) => set({ activeOrganization: org }),
}));

// 3. Theme Store
export type ThemeMode = 'light' | 'dark' | 'system';

interface ThemeState {
  theme: ThemeMode;
  setTheme: (theme: ThemeMode) => void;
}

export const useThemeStore = create<ThemeState>((set) => ({
  theme: 'system',
  setTheme: (theme) => set({ theme }),
}));

// 4. Sidebar Store
interface SidebarState {
  isOpen: boolean;
  toggle: () => void;
  setOpen: (open: boolean) => void;
}

export const useSidebarStore = create<SidebarState>((set) => ({
  isOpen: true,
  toggle: () => set((state) => ({ isOpen: !state.isOpen })),
  setOpen: (isOpen) => set({ isOpen }),
}));

// 5. Notification Store
interface NotificationItem {
  id: string;
  title: string;
  message: string;
  type: 'info' | 'warning' | 'error';
  timestamp: string;
  read: boolean;
}

interface NotificationState {
  notifications: NotificationItem[];
  addNotification: (item: Omit<NotificationItem, 'id' | 'timestamp' | 'read'>) => void;
  markAsRead: (id: string) => void;
  clearAll: () => void;
}

export const useNotificationStore = create<NotificationState>((set) => ({
  notifications: [],
  addNotification: (item) => set((state) => ({
    notifications: [
      {
        ...item,
        id: Math.random().toString(36).substring(7),
        timestamp: new Date().toISOString(),
        read: false,
      },
      ...state.notifications
    ]
  })),
  markAsRead: (id) => set((state) => ({
    notifications: state.notifications.map(n => n.id === id ? { ...n, read: true } : n)
  })),
  clearAll: () => set({ notifications: [] }),
}));
