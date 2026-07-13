import { BaseRegistry } from './BaseRegistry';
import { ModuleId } from '../index';

export interface PermissionItem {
  readonly id: string;
  readonly module: ModuleId;
  readonly resource: string;
  readonly action: string;
  readonly category: string;
  readonly description: string;
  readonly order: number;
}

export class PermissionRegistry extends BaseRegistry<PermissionItem> {
  private static instance = new PermissionRegistry();
  protected eventNamespace = 'permissions';

  private constructor() {
    super();
  }

  public static register(permission: PermissionItem): void {
    this.instance.register(permission);
  }

  public static unregister(moduleName: ModuleId): void {
    this.instance.unregister(moduleName);
  }

  public static find(id: string): Readonly<PermissionItem> | null {
    return this.instance.find(id);
  }

  public static getAll(): ReadonlyArray<PermissionItem> {
    return this.instance.getAll();
  }

  public static clear(): void {
    this.instance.clear();
  }

  public static getStats() {
    return this.instance.stats();
  }

  public static subscribe(listener: () => void) {
    return this.instance.subscribe(listener);
  }
}
export default PermissionRegistry;
