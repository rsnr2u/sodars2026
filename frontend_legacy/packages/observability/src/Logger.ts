import { ObservabilityAdapter, Severity } from './types';
import { ConsoleAdapter } from './adapters/ConsoleAdapter';

export class Logger {
  private static adapters: Set<ObservabilityAdapter> = new Set([new ConsoleAdapter()]);

  public static addAdapter(adapter: ObservabilityAdapter): void {
    this.adapters.add(adapter);
  }

  public static removeAdapter(adapter: ObservabilityAdapter): void {
    this.adapters.delete(adapter);
  }

  public static debug(message: string, context?: Record<string, unknown>): void {
    this.adapters.forEach(a => {
      try {
        a.log(message, Severity.Debug, context);
      } catch (err) {
        console.error('[Logger] Failed to write log to adapter:', err);
      }
    });
  }

  public static info(message: string, context?: Record<string, unknown>): void {
    this.adapters.forEach(a => {
      try {
        a.log(message, Severity.Info, context);
      } catch (err) {
        console.error('[Logger] Failed to write log to adapter:', err);
      }
    });
  }

  public static warn(message: string, context?: Record<string, unknown>): void {
    this.adapters.forEach(a => {
      try {
        a.log(message, Severity.Warning, context);
      } catch (err) {
        console.error('[Logger] Failed to write log to adapter:', err);
      }
    });
  }

  public static error(message: string, context?: Record<string, unknown>): void {
    this.adapters.forEach(a => {
      try {
        a.log(message, Severity.Error, context);
      } catch (err) {
        console.error('[Logger] Failed to write log to adapter:', err);
      }
    });
  }

  public static critical(message: string, context?: Record<string, unknown>): void {
    this.adapters.forEach(a => {
      try {
        a.log(message, Severity.Critical, context);
      } catch (err) {
        console.error('[Logger] Failed to write log to adapter:', err);
      }
    });
  }
}
export default Logger;
