import { Severity, Telemetry } from '@sodars/observability';

const NAMESPACE = 'provider';

export class ProviderTelemetry {
  public static trackCreated(providerId: string): void {
    Telemetry.track(
      'command:executed',
      Severity.Info,
      { action: 'provider.created', providerId },
      NAMESPACE,
    );
  }

  public static trackUpdated(providerId: string): void {
    Telemetry.track(
      'command:executed',
      Severity.Info,
      { action: 'provider.updated', providerId },
      NAMESPACE,
    );
  }

  public static trackDeleted(providerId: string): void {
    Telemetry.track(
      'command:executed',
      Severity.Warning,
      { action: 'provider.deleted', providerId },
      NAMESPACE,
    );
  }

  public static trackVerified(providerId: string): void {
    Telemetry.track(
      'command:executed',
      Severity.Info,
      { action: 'provider.verified', providerId },
      NAMESPACE,
    );
  }

  public static trackRejected(providerId: string, reason: string): void {
    Telemetry.track(
      'command:executed',
      Severity.Warning,
      { action: 'provider.rejected', providerId, reason },
      NAMESPACE,
    );
  }

  public static trackSuspended(providerId: string): void {
    Telemetry.track(
      'command:executed',
      Severity.Warning,
      { action: 'provider.suspended', providerId },
      NAMESPACE,
    );
  }

  public static trackActivated(providerId: string): void {
    Telemetry.track(
      'command:executed',
      Severity.Info,
      { action: 'provider.activated', providerId },
      NAMESPACE,
    );
  }
}
export default ProviderTelemetry;
