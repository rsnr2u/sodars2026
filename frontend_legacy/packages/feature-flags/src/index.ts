export class FeatureFlags {
  private static flags: Map<string, boolean> = new Map([
    ['wallet.beta', true],
    ['maps.live', true],
    ['ai.dashboard', false]
  ]);

  public static isEnabled(flag: string): boolean {
    return this.flags.get(flag) ?? false;
  }

  public static setFlag(flag: string, enabled: boolean): void {
    this.flags.set(flag, enabled);
  }
}
