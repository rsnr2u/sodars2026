export interface AuditLog {
  readonly id: string;
  readonly timestamp: number;
  readonly actorId: string;
  readonly action: string;
  readonly details: string;
}
