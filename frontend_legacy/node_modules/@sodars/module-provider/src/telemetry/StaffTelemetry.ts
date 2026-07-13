import { Severity, Telemetry } from '@sodars/observability';

const NAMESPACE = 'provider.staff';

export class StaffTelemetry {
  public static trackCreated(providerId: string, staffId: string): void {
    Telemetry.track(
      'command:executed',
      Severity.Info,
      { action: 'staff.created', providerId, staffId },
      NAMESPACE,
    );
  }

  public static trackUpdated(providerId: string, staffId: string): void {
    Telemetry.track(
      'command:executed',
      Severity.Info,
      { action: 'staff.updated', providerId, staffId },
      NAMESPACE,
    );
  }

  public static trackDeleted(providerId: string, staffId: string): void {
    Telemetry.track(
      'command:executed',
      Severity.Warning,
      { action: 'staff.deleted', providerId, staffId },
      NAMESPACE,
    );
  }

  public static trackAssigned(providerId: string, staffId: string, branchId: string): void {
    Telemetry.track(
      'command:executed',
      Severity.Info,
      { action: 'staff.assigned', providerId, staffId, branchId },
      NAMESPACE,
    );
  }

  public static trackTransferred(providerId: string, staffId: string, newBranchId: string): void {
    Telemetry.track(
      'command:executed',
      Severity.Info,
      { action: 'staff.transferred', providerId, staffId, newBranchId },
      NAMESPACE,
    );
  }

  public static trackActivated(providerId: string, staffId: string): void {
    Telemetry.track(
      'command:executed',
      Severity.Info,
      { action: 'staff.activated', providerId, staffId },
      NAMESPACE,
    );
  }

  public static trackDeactivated(providerId: string, staffId: string): void {
    Telemetry.track(
      'command:executed',
      Severity.Warning,
      { action: 'staff.deactivated', providerId, staffId },
      NAMESPACE,
    );
  }
}
export default StaffTelemetry;
