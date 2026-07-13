export type ThemeMode = 'light' | 'dark' | 'high-contrast';

export class ThemeRegistry {
  private static mode: ThemeMode = 'light';
  private static listeners = new Set<(mode: ThemeMode) => void>();

  public static getTheme(): ThemeMode {
    return this.mode;
  }

  public static setTheme(mode: ThemeMode) {
    this.mode = mode;
    this.listeners.forEach(fn => fn(mode));
  }

  public static subscribe(fn: (mode: ThemeMode) => void) {
    this.listeners.add(fn);
    return () => this.listeners.delete(fn);
  }
}
