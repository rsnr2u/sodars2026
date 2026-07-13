export class Telemetry {
  public static trackEvent(eventName: string, properties?: Record<string, unknown>): void {
    console.log(`[Telemetry] Event "${eventName}" logged.`, properties ?? {});
  }

  public static trackException(error: Error, info?: Record<string, unknown>): void {
    console.error(`[Telemetry] Exception captured: ${error.message}`, error.stack, info ?? {});
  }

  public static trackLatency(metricName: string, durationMs: number): void {
    console.log(`[Telemetry] Latency Metric: ${metricName} took ${durationMs}ms`);
  }
}
