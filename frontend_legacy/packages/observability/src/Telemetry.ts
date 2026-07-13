import { ObservabilityAdapter, TelemetryPayload, Severity, TelemetryEvent } from './types';
import { ConsoleAdapter } from './adapters/ConsoleAdapter';
import { EventBus } from '@sodars/events';

export class Telemetry {
  private static adapters: Set<ObservabilityAdapter> = new Set([new ConsoleAdapter()]);

  public static addAdapter(adapter: ObservabilityAdapter): void {
    this.adapters.add(adapter);
  }

  public static removeAdapter(adapter: ObservabilityAdapter): void {
    this.adapters.delete(adapter);
  }

  public static track(
    event: TelemetryEvent,
    severity: Severity = Severity.Info,
    properties?: Record<string, unknown>,
    module?: string,
    duration?: number,
    correlationId?: string
  ): void {
    const payload: TelemetryPayload = {
      timestamp: Date.now(),
      event,
      severity,
      properties,
      module,
      duration,
      correlationId
    };

    // 1. Notify observability backends
    this.adapters.forEach(a => {
      try {
        a.trackEvent(payload);
      } catch (err) {
        console.error('[Telemetry] Failed to track event in adapter:', err);
      }
    });

    // 2. Publish to internal EventBus for Diagnostics viewer streaming
    EventBus.publish('telemetry:event', payload);
  }

  public static trackError(error: Error, severity: Severity = Severity.Error): void {
    this.adapters.forEach(a => {
      try {
        a.trackError(error, severity);
      } catch (err) {
        console.error('[Telemetry] Failed to track error in adapter:', err);
      }
    });

    EventBus.publish('telemetry:error', {
      timestamp: Date.now(),
      errorName: error.name,
      message: error.message,
      stack: error.stack,
      severity
    });
  }
}
export default Telemetry;
