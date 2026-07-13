import { ModuleId, RegistryItem } from '../index';

export interface RegistryStats {
  readonly total: number;
  readonly modules: number;
  readonly version: number;
  readonly lastUpdated: number;
}

export type RegistryListener<T> = (
  items: ReadonlyArray<T>,
  stats: RegistryStats
) => void;

export abstract class BaseRegistry<T extends RegistryItem> {
  protected items: Map<string, T> = new Map();
  protected version = 0;
  protected lastUpdated = Date.now();
  protected listeners: Set<RegistryListener<T>> = new Set();
  protected abstract eventNamespace: string;

  public register(item: T): void {
    this.items.set(item.id, this.deepFreeze({ ...item }));
    this.incrementVersion();
  }

  public replace(item: T): void {
    this.items.set(item.id, this.deepFreeze({ ...item }));
    this.incrementVersion();
  }

  public unregister(moduleName: ModuleId): void {
    let changed = false;
    for (const [id, item] of this.items.entries()) {
      if (item.module === moduleName) {
        this.items.delete(id);
        changed = true;
      }
    }
    if (changed) this.incrementVersion();
  }

  public find(id: string): Readonly<T> | null {
    return this.items.get(id) ?? null;
  }

  public snapshot(): ReadonlyArray<T> {
    return this.getAll();
  }

  protected cachedList: ReadonlyArray<T> | null = null;
  protected cachedListVersion = -1;

  public getAll(): ReadonlyArray<T> {
    if (this.cachedList && this.cachedListVersion === this.version) {
      return this.cachedList;
    }
    const list = Array.from(this.items.values());
    this.cachedList = Object.freeze(
      list.sort((a, b) => (a.order ?? 100) - (b.order ?? 100))
    );
    this.cachedListVersion = this.version;
    return this.cachedList;
  }

  public subscribe(listener: RegistryListener<T>): () => void {
    this.listeners.add(listener);
    return () => this.listeners.delete(listener);
  }

  public stats(): RegistryStats {
    const uniqueModules = new Set(Array.from(this.items.values()).map(i => i.module));
    return {
      total: this.items.size,
      modules: uniqueModules.size,
      version: this.version,
      lastUpdated: this.lastUpdated,
    };
  }

  public clear(): void {
    this.items.clear();
    this.incrementVersion();
  }

  protected incrementVersion(): void {
    this.version++;
    this.lastUpdated = Date.now();
    const currentStats = this.stats();
    const currentItems = this.getAll();
    this.listeners.forEach(listener => {
      try {
        listener(currentItems, currentStats);
      } catch (err) {
        console.error(`[BaseRegistry:${this.eventNamespace}] Error in update listener:`, err);
      }
    });
  }

  protected deepFreeze<U>(obj: U): Readonly<U> {
    if (obj === null || typeof obj !== 'object') {
      return obj as any;
    }
    Object.freeze(obj);
    Object.getOwnPropertyNames(obj).forEach((prop) => {
      const propVal = (obj as any)[prop];
      if (propVal !== null &&
          (typeof propVal === 'object' || typeof propVal === 'function') &&
          !Object.isFrozen(propVal)) {
        this.deepFreeze(propVal);
      }
    });
    return obj as Readonly<U>;
  }
}
