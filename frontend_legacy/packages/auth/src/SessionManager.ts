import { useAuthStore, useTenantStore, usePermissionStore, useSessionStore } from '@sodars/store';
import { EventBus } from '@sodars/events';
import { SecureStorage, JwtParser } from '@sodars/security';
import { AuthEvents } from './AuthEvents';
import { UserDTO } from '@sodars/contracts';

export class SessionManager {
  private static idleTimer: any = null;
  private static refreshTimer: any = null;
  private static IDLE_TIMEOUT_MS = 15 * 60 * 1000; // 15 minutes default idle timeout

  public static initialize(): void {
    console.log('[SessionManager] Initializing session restore from secure storage...');
    const token = SecureStorage.getItem('sodars_access_token');
    const refreshToken = SecureStorage.getItem('sodars_refresh_token');
    const rawUser = SecureStorage.getItem('sodars_user_profile');

    if (token && refreshToken && rawUser) {
      try {
        const user = JSON.parse(rawUser) as UserDTO;
        if (JwtParser.isExpired(token)) {
          console.warn('[SessionManager] Loaded token is expired. Triggering refresh...');
          useSessionStore.getState().setState('Expired');
          this.triggerRefresh();
        } else {
          useAuthStore.getState().setSession(token, refreshToken, user);
          usePermissionStore.getState().setPermissions(user.permissions, user.roles);
          useSessionStore.getState().setState('Authenticated');
          this.startTimers();
        }
      } catch (err) {
        console.error('[SessionManager] Error parsing user session profile:', err);
        this.clearSession();
      }
    } else {
      useSessionStore.getState().setState('Unauthenticated');
    }
  }

  public static login(token: string, refreshToken: string, user: UserDTO): void {
    console.log('[SessionManager] Logging user in and storing session...');
    useSessionStore.getState().setState('Authenticating');

    SecureStorage.setItem('sodars_access_token', token);
    SecureStorage.setItem('sodars_refresh_token', refreshToken);
    SecureStorage.setItem('sodars_user_profile', JSON.stringify(user));

    useAuthStore.getState().setSession(token, refreshToken, user);
    usePermissionStore.getState().setPermissions(user.permissions, user.roles);
    useSessionStore.getState().setState('Authenticated');

    // Default tenant selection on login if present
    if (user.organizations && user.organizations.length > 0) {
      const org = user.organizations[0];
      useTenantStore.getState().setActiveOrganization(org);
      EventBus.publish(AuthEvents.TenantChanged, org);
    }

    this.startTimers();
    EventBus.publish(AuthEvents.UserLoggedIn, user);
  }

  public static logout(): void {
    console.log('[SessionManager] Logging user out and purging session...');
    this.clearSession();
    EventBus.publish(AuthEvents.UserLoggedOut, null);
  }

  private static clearSession(): void {
    this.stopTimers();
    SecureStorage.removeItem('sodars_access_token');
    SecureStorage.removeItem('sodars_refresh_token');
    SecureStorage.removeItem('sodars_user_profile');
    useAuthStore.getState().clearSession();
    usePermissionStore.getState().clearPermissions();
    useTenantStore.getState().setActiveOrganization(null);
    useTenantStore.getState().setActiveBranch(null);
    useSessionStore.getState().setState('LoggedOut');
  }

  public static startTimers(): void {
    this.stopTimers();
    this.startIdleTimer();
    this.startRefreshTimer();
  }

  public static stopTimers(): void {
    if (this.idleTimer) {
      clearTimeout(this.idleTimer);
      this.idleTimer = null;
    }
    if (this.refreshTimer) {
      clearInterval(this.refreshTimer);
      this.refreshTimer = null;
    }
  }

  private static startIdleTimer(): void {
    // Reset idle timer on user activity listeners
    const resetTimer = () => {
      if (useSessionStore.getState().state !== 'Authenticated') return;
      if (this.idleTimer) clearTimeout(this.idleTimer);
      this.idleTimer = setTimeout(() => {
        console.warn('[SessionManager] User has been idle. Logging out due to timeout...');
        EventBus.publish(AuthEvents.SessionExpired, 'IdleTimeout');
        this.clearSession();
      }, this.IDLE_TIMEOUT_MS);
    };

    window.addEventListener('mousemove', resetTimer);
    window.addEventListener('keydown', resetTimer);
    window.addEventListener('click', resetTimer);

    // Initial trigger
    resetTimer();
  }

  private static startRefreshTimer(): void {
    // Refresh tokens 1 minute before expiration
    this.refreshTimer = setInterval(() => {
      this.triggerRefresh();
    }, 60000); // Check every minute
  }

  public static async triggerRefresh(): Promise<boolean> {
    const refreshToken = SecureStorage.getItem('sodars_refresh_token');
    if (!refreshToken) {
      console.warn('[SessionManager] No refresh token found. Aborting refresh...');
      return false;
    }

    console.log('[SessionManager] Initiating background JWT refresh...');
    useSessionStore.getState().setState('Refreshing');
    // Simulated token refresh baseline endpoint logic (Sprint 3 integration)
    return true;
  }
}
