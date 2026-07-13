import { ObservabilityAdapter, Severity, TelemetryPayload } from '../types';

export class ConsoleAdapter implements ObservabilityAdapter {
  public log(message: string, severity: Severity, context?: Record<string, unknown>): void {
    const formatted = `[LOG:${Severity[severity]}] ${message}`;
    if (severity === Severity.Error || severity === Severity.Critical) {
      console.error(formatted, context || '');
    } else if (severity === Severity.Warning) {
      console.warn(formatted, context || '');
    } else {
      console.log(formatted, context || '');
    }
  }

  public trackEvent(payload: TelemetryPayload): void {
    console.log(`[TELEMETRY:${payload.event}]`, {
      timestamp: new Date(payload.timestamp).toISOString(),
      severity: Severity[payload.severity],
      module: payload.module || 'system',
      correlationId: payload.correlationId || '',
      duration: payload.duration !== undefined ? `${payload.duration}ms` : undefined,
      properties: payload.properties || {}
    });
  }

  public trackError(error: Error, severity: Severity = Severity.Error): void {
    console.error(`[ERROR:${Severity[severity]}] ${error.name}: ${error.message}`, error.stack || '');
  }
}
export default ConsoleAdapter;
