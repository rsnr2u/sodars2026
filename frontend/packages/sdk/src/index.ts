import React from 'react';

// Navigation Registry
export interface MenuItem {
  id: string;
  label: string;
  path: string;
  icon: string;
  permission?: string;
  priority?: number;
}

export class NavigationRegistry {
  private static items: MenuItem[] = [];

  public static register(item: MenuItem): void {
    if (!this.items.some(i => i.id === item.id)) {
      this.items.push(item);
      this.items.sort((a, b) => (a.priority ?? 100) - (b.priority ?? 100));
    }
  }

  public static getItems(): MenuItem[] {
    return this.items;
  }
}

// Widget SDK
export interface WidgetConfig {
  id: string;
  name: string;
  component: React.ComponentType<unknown>;
  permissions?: string[];
  defaultLayout?: { w: number; h: number };
}

export class WidgetSDK {
  private static widgets: Map<string, WidgetConfig> = new Map();

  public static register(widget: WidgetConfig): void {
    this.widgets.set(widget.id, widget);
  }

  public static getWidgets(): WidgetConfig[] {
    return Array.from(this.widgets.values());
  }
}

// Module SDK
export interface SodarsModule {
  name: string;
  boot(): void;
}

export class ModuleSDK {
  private static modules: Map<string, SodarsModule> = new Map();

  public static register(module: SodarsModule): void {
    this.modules.set(module.name, module);
    module.boot();
  }

  public static getModules(): SodarsModule[] {
    return Array.from(this.modules.values());
  }
}
