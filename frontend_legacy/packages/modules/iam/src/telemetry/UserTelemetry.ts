import { Telemetry, Severity } from '@sodars/observability';
import { User } from '../types/user';

export class UserTelemetry {
  public static userCreated(user: User): void {
    Telemetry.track('command:executed', Severity.Info, { action: 'user:created', userId: user.id }, 'iam');
  }

  public static userLocked(user: User): void {
    Telemetry.track('command:executed', Severity.Warning, { action: 'user:locked', userId: user.id }, 'iam');
  }
}
export default UserTelemetry;
