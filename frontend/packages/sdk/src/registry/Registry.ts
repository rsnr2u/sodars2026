import { ModuleId } from '../index';
import { RegistryStats, RegistryListener } from './BaseRegistry';

export interface Registry<T> {
  register(item: T): void;
  replace(item: T): void;
  unregister(moduleName: ModuleId): void;
  find(id: string): Readonly<T> | null;
  getAll(): ReadonlyArray<T>;
  subscribe(listener: RegistryListener<T>): () => void;
  stats(): RegistryStats;
  clear(): void;
}
