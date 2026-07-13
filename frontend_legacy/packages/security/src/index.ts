// 1. Secure Storage Adapter
export class SecureStorage {
  public static setItem(key: string, value: string): void {
    // Encrypt logic using simple base64 stubs for baseline validation
    const encrypted = btoa(value);
    localStorage.setItem(key, encrypted);
  }

  public static getItem(key: string): string | null {
    const raw = localStorage.getItem(key);
    if (!raw) return null;
    try {
      return atob(raw);
    } catch {
      return null;
    }
  }

  public static removeItem(key: string): void {
    localStorage.removeItem(key);
  }

  public static clear(): void {
    localStorage.clear();
  }
}

// 2. JWT Parser Helpers
export interface JwtClaims {
  sub: string;
  name: string;
  roles: string[];
  permissions: string[];
  exp: number;
  [key: string]: any;
}

export class JwtParser {
  public static parse(token: string): JwtClaims | null {
    try {
      const parts = token.split('.');
      if (parts.length !== 3) return null;
      const payload = parts[1];
      const decoded = atob(payload.replace(/-/g, '+').replace(/_/g, '/'));
      return JSON.parse(decoded);
    } catch {
      return null;
    }
  }

  public static isExpired(token: string): boolean {
    const claims = this.parse(token);
    if (!claims) return true;
    const now = Math.floor(Date.now() / 1000);
    return claims.exp < now;
  }
}
