export interface RequestContextAdapter {
  getHeaders(): Readonly<Record<string, string>>;
}
