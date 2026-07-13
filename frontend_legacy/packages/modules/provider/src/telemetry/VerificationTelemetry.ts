import { Severity, Telemetry } from '@sodars/observability';

const NAMESPACE = 'provider.verification';

export class VerificationTelemetry {
  public static trackStarted(providerId: string, verificationId: string): void {
    Telemetry.track(
      'command:executed',
      Severity.Info,
      { action: 'verification.started', providerId, verificationId },
      NAMESPACE,
    );
  }

  public static trackStepUpdated(
    providerId: string,
    verificationId: string,
    stepId: string,
    status: string
  ): void {
    Telemetry.track(
      'command:executed',
      Severity.Info,
      { action: 'verification.step.updated', providerId, verificationId, stepId, status },
      NAMESPACE,
    );
  }

  public static trackApproved(providerId: string, verificationId: string): void {
    Telemetry.track(
      'command:executed',
      Severity.Info,
      { action: 'verification.approved', providerId, verificationId },
      NAMESPACE,
    );
  }

  public static trackRejected(
    providerId: string,
    verificationId: string,
    reason: string
  ): void {
    Telemetry.track(
      'command:executed',
      Severity.Warning,
      { action: 'verification.rejected', providerId, verificationId, reason },
      NAMESPACE,
    );
  }

  public static trackRestarted(providerId: string, verificationId: string): void {
    Telemetry.track(
      'command:executed',
      Severity.Info,
      { action: 'verification.restarted', providerId, verificationId },
      NAMESPACE,
    );
  }
}
export default VerificationTelemetry;
