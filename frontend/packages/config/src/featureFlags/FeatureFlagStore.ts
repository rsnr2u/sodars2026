export type FeatureFlagKey =
  | 'enableCommandPalette'
  | 'enableRealtimeNotifications'
  | 'enableWalletLedger';

export interface FeatureFlagContext {
  readonly organizationId?: string;
  readonly branchId?: string;
  readonly workspaceId?: string;
  readonly userId?: string;
  readonly role?: string;
  readonly environment?: string;
  readonly module?: string;
}

export class FeatureFlagStore {
  private static flags: Map<FeatureFlagKey, boolean | ((context: FeatureFlagContext) => boolean)> = new Map([
    ['enableCommandPalette', true],
    ['enableRealtimeNotifications', true],
    ['enableWalletLedger', false]
  ]);

  public static set(key: FeatureFlagKey, value: boolean | ((context: FeatureFlagContext) => boolean)): void {
    this.flags.set(key, value);
  }

  public static evaluate(key: FeatureFlagKey, context: FeatureFlagContext): boolean {
    const entry = this.flags.get(key);
    if (entry === undefined) return false;
    if (typeof entry === 'function') {
      return entry(context);
    }
    return entry;
  }

  public static evaluateMany(keys: FeatureFlagKey[], context: FeatureFlagContext): Partial<Record<FeatureFlagKey, boolean>> {
    const results: Partial<Record<FeatureFlagKey, boolean>> = {};
    for (const key of keys) {
      results[key] = this.evaluate(key, context);
    }
    return results;
  }
}
export default FeatureFlagStore;
