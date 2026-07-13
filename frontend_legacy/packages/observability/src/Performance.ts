import { Telemetry } from './Telemetry';
import { Severity, PerformanceEvents } from './types';

export class Performance {
  private static marks: Map<string, number> = new Map();

  public static startMark(name: string): void {
    this.marks.set(name, Date.now());
  }

  public static endMark(
    name: string,
    event: PerformanceEvents = PerformanceEvents.QueryCompleted,
    properties?: Record<string, unknown>,
    module?: string
  ): number | undefined {
    const startTime = this.marks.get(name);
    if (startTime === undefined) {
      console.warn(`[Performance] Start mark "${name}" not found.`);
      return undefined;
    }

    const duration = Date.now() - startTime;
    this.marks.delete(name);

    // Auto log to Telemetry
    Telemetry.track(
      event,
      Severity.Info,
      { ...properties, markName: name },
      module,
      duration
    );

    return duration;
  }
}
export default Performance;
