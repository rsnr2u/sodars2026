import React from 'react';
import { IconType } from '@sodars/icons';

// 1. Badge Definition
export interface BadgeDefinition {
  value: string | number;
  variant: 'primary' | 'success' | 'warning' | 'danger' | 'info';
  pulse?: boolean;
  tooltip?: string;
}

// 2. Navigation Node
export interface NavigationNode {
  id: string; // Hierarchical dot-separated notation (e.g. 'crm.leads')
  module: string; // Ownership module namespace
  title: string;
  route?: string;
  icon?: IconType;
  parent?: string;
  order: number;
  permission?: string;
  featureFlag?: string;
  badge?: BadgeDefinition;
  hidden?: boolean;
  disabled?: boolean;
  external?: boolean;
  target?: "_self" | "_blank";
  children?: NavigationNode[];
}

// 3. Navigation Registry API
export class NavigationRegistry {
  private static rawNodes: Map<string, NavigationNode> = new Map();
  private static cachedTree: ReadonlyArray<NavigationNode> | null = null;
  private static cachedFlatList: ReadonlyArray<NavigationNode> | null = null;

  public static register(node: NavigationNode): void {
    if (this.rawNodes.has(node.id)) {
      console.warn(`[NavigationRegistry] Node with ID "${node.id}" already exists. Use replace() instead.`);
      return;
    }
    this.rawNodes.set(node.id, { ...node });
    this.invalidateCache();
  }

  public static unregister(moduleName: string): void {
    let changed = false;
    for (const [id, node] of this.rawNodes.entries()) {
      if (node.module === moduleName) {
        this.rawNodes.delete(id);
        changed = true;
      }
    }
    if (changed) this.invalidateCache();
  }

  public static replace(node: NavigationNode): void {
    this.rawNodes.set(node.id, { ...node });
    this.invalidateCache();
  }

  public static find(id: string): Readonly<NavigationNode> | null {
    const node = this.rawNodes.get(id);
    return node ? Object.freeze({ ...node }) : null;
  }

  public static getFlatList(): ReadonlyArray<NavigationNode> {
    if (this.cachedFlatList) return this.cachedFlatList;

    const list = Array.from(this.rawNodes.values())
      .map(node => Object.freeze({ ...node }))
      .sort((a, b) => a.order - b.order);

    this.cachedFlatList = Object.freeze(list);
    return this.cachedFlatList;
  }

  public static getTree(): ReadonlyArray<NavigationNode> {
    if (this.cachedTree) return this.cachedTree;

    const flatList = this.getFlatList().map(node => ({ ...node, children: [] as NavigationNode[] }));
    const rootNodes: NavigationNode[] = [];
    const nodeMap = new Map<string, NavigationNode>();

    for (const node of flatList) {
      nodeMap.set(node.id, node);
    }

    for (const node of flatList) {
      if (node.parent && nodeMap.has(node.parent)) {
        const parentNode = nodeMap.get(node.parent)!;
        parentNode.children = parentNode.children ?? [];
        parentNode.children.push(node);
        parentNode.children.sort((a, b) => a.order - b.order);
      } else {
        rootNodes.push(node);
      }
    }

    rootNodes.sort((a, b) => a.order - b.order);

    // Deep freeze the constructed tree to ensure immutability
    const deepFreeze = (arr: NavigationNode[]): ReadonlyArray<NavigationNode> => {
      arr.forEach(node => {
        if (node.children) {
          deepFreeze(node.children);
        }
        Object.freeze(node);
      });
      return Object.freeze(arr);
    };

    this.cachedTree = deepFreeze(rootNodes);
    return this.cachedTree;
  }

  public static clear(): void {
    this.rawNodes.clear();
    this.invalidateCache();
  }

  private static invalidateCache(): void {
    this.cachedTree = null;
    this.cachedFlatList = null;
  }
}

// 4. Widget SDK
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

// 5. Module SDK
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
