export interface RouterAdapter {
  navigate(to: string): Promise<void>;
  getCurrentPath(): string;
}
