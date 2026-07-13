export interface LicenseInfo {
  tier: 'Standard' | 'Enterprise';
  status: 'Active' | 'Expired';
  expiry: number;
}

export class LicenseRegistry {
  private static license: LicenseInfo = {
    tier: 'Enterprise',
    status: 'Active',
    expiry: Date.now() + 365 * 24 * 60 * 60 * 1000
  };

  public static get(): LicenseInfo {
    return this.license;
  }
}
