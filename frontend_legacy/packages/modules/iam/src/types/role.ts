export interface Role {
  readonly id: string;
  readonly name: string;
  readonly permissions: string[];
  readonly description: string;
}
