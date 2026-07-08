export interface QueryClientAdapter {
  getQueryData<T>(key: string[]): T | undefined;
  setQueryData<T>(key: string[], data: T): void;
}
