export class FeatureFlagsRegistry {
  private static flags = new Map<string, boolean>();

  public static set(flag: string, value: boolean) {
    this.flags.set(flag, value);
  }

  public static isEnabled(flag: string): boolean {
    return this.flags.get(flag) ?? false;
  }
}
