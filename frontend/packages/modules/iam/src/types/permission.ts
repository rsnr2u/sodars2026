export interface Permission {
  readonly id: string;
  readonly name: string;
  readonly resource: string;
  readonly action: string;
  readonly description: string;
}
