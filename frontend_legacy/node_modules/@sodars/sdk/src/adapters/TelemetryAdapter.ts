export interface TelemetryAdapter {
  trackEvent(name: string, properties?: Record<string, unknown>): void;
  trackError(error: Error, severity?: string): void;
}
