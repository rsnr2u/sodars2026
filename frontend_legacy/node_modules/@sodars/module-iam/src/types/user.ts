export interface User {
  readonly id: string;
  readonly name: string;
  readonly email: string;
  readonly roleId: string;
  readonly isLocked: boolean;
}
