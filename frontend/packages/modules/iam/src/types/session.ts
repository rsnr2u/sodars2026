export interface Session {
  readonly id: string;
  readonly userId: string;
  readonly device: string;
  readonly ipAddress: string;
  readonly lastActive: number;
}
