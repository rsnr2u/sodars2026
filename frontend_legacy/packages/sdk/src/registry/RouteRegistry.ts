import { BaseRegistry } from './BaseRegistry';
import { ModuleId } from '../index';
import { IconType } from '@sodars/icons';
import React from 'react';

export interface RouteItem {
  readonly id: string;
  readonly module: ModuleId;
  readonly path: string;
  readonly component: React.ComponentType<any>;
  readonly title?: string;
  readonly icon?: IconType;
  readonly permission?: string;
  readonly featureFlag?: string;
  readonly layout?: 'shell' | 'blank';
  readonly order: number;
}

export class RouteRegistry extends BaseRegistry<RouteItem> {
  private static instance = new RouteRegistry();
  protected eventNamespace = 'routes';

  private constructor() {
    super();
  }

  public static register(route: RouteItem): void {
    this.instance.register(route);
  }

  public static unregister(moduleName: ModuleId): void {
    this.instance.unregister(moduleName);
  }

  public static find(id: string): Readonly<RouteItem> | null {
    return this.instance.find(id);
  }

  public static getAll(): ReadonlyArray<RouteItem> {
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
export default RouteRegistry;
