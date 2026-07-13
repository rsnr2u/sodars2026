export type Identifier<T extends string> = string & {
  readonly __type: T;
};

export type EntityId = string;
export type TenantId = string;
export type UserId = string;
