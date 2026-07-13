import { ObservabilityAdapter, Severity, TelemetryPayload } from '../types';

export class SentryAdapter implements ObservabilityAdapter {
  public log(message: string, severity: Severity, context?: Record<string, unknown>): void {
    // Sentry SDK custom mock trace bindings
    const levelMap: Record<Severity, string> = {
      [Severity.Debug]: 'debug',
      [Severity.Info]: 'info',
      [Severity.Warning]: 'warning',
      [Severity.Error]: 'error',
      [Severity.Critical]: 'fatal'
    };
    console.debug(`[Sentry Mock Log:${levelMap[severity]}] ${message}`, context || '');
  }

  public trackEvent(payload: TelemetryPayload): void {
    console.debug(`[Sentry Mock Event:${payload.event}]`, payload);
  }

  public trackError(error: Error, severity: Severity = Severity.Error): void {
    console.debug(`[Sentry Mock Error:${Severity[severity]}]`, error);
  }
}
export default SentryAdapter;
