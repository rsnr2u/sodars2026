import { ObservabilityAdapter, Severity, TelemetryPayload } from '../types';

export class OpenTelemetryAdapter implements ObservabilityAdapter {
  public log(message: string, severity: Severity, context?: Record<string, unknown>): void {
    console.debug(`[OTEL Mock Log:${Severity[severity]}] ${message}`, context || '');
  }

  public trackEvent(payload: TelemetryPayload): void {
    console.debug(`[OTEL Mock Event:${payload.event}]`, payload);
  }

  public trackError(error: Error, severity: Severity = Severity.Error): void {
    console.debug(`[OTEL Mock Error:${Severity[severity]}]`, error);
  }
}
export default OpenTelemetryAdapter;
