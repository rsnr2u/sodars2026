import React from 'react';
import { BaseRegistry } from './BaseRegistry';
import { ModuleId } from '../index';

export interface DashboardWidgetProps {
  readonly organizationId: string;
  readonly branchId: string;
}

export interface DashboardWidget {
  readonly id: string;
  readonly module: ModuleId;
  readonly title: string;
  readonly component: React.ComponentType<DashboardWidgetProps>;
  readonly loader?: () => Promise<React.ComponentType<DashboardWidgetProps>>;
  readonly width: 1 | 2 | 3 | 4;
  readonly height: number;
  readonly minWidth?: 1 | 2 | 3 | 4;
  readonly minHeight?: number;
  readonly order: number;
  readonly x?: number;
  readonly y?: number;
  readonly cacheTime?: number;
  readonly staleTime?: number;
  readonly permission?: string;
  readonly featureFlag?: string;
  readonly enabled?: (context: DashboardWidgetProps) => boolean;
  readonly initialize?: () => Promise<void>;
  readonly dispose?: () => Promise<void>;
}

export class WidgetRegistry extends BaseRegistry<DashboardWidget> {
  private static instance = new WidgetRegistry();
  protected eventNamespace = 'widget';

  private constructor() {
    super();
  }

  public static register(widget: DashboardWidget): void {
    this.instance.register(widget);
  }

  public static replace(widget: DashboardWidget): void {
    this.instance.replace(widget);
  }

  public static unregister(moduleName: ModuleId): void {
    this.instance.unregister(moduleName);
  }

  public static find(id: string): Readonly<DashboardWidget> | null {
    return this.instance.find(id);
  }

  public static getAll(): ReadonlyArray<DashboardWidget> {
    return this.instance.getAll();
  }

  public static getWidgets(): ReadonlyArray<DashboardWidget> {
    return this.instance.getAll();
  }

  public static clear(): void {
    this.instance.clear();
  }

  public static getStats() {
    return this.instance.stats();
  }

  public static subscribe(listener: any) {
    return this.instance.subscribe(listener);
  }
}
export default WidgetRegistry;
