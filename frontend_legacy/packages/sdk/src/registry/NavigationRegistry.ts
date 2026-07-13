import { BaseRegistry } from './BaseRegistry';
import type { NavigationNode, ModuleId } from '../index';

export class NavigationRegistry extends BaseRegistry<NavigationNode> {
  private static instance = new NavigationRegistry();
  protected eventNamespace = 'navigation';

  private static cachedTree: ReadonlyArray<NavigationNode> | null = null;
  private static cachedVersion = -1;

  private constructor() {
    super();
  }

  public static register(node: NavigationNode): void {
    this.instance.register(node);
  }

  public static replace(node: NavigationNode): void {
    this.instance.replace(node);
  }

  public static unregister(moduleName: ModuleId): void {
    this.instance.unregister(moduleName);
  }

  public static find(id: string): Readonly<NavigationNode> | null {
    return this.instance.find(id);
  }

  public static getFlatList(): ReadonlyArray<NavigationNode> {
    return this.instance.getAll();
  }

  public static getTree(): ReadonlyArray<NavigationNode> {
    // If the cache matches the current registry state version, return it immediately to prevent React infinite loops.
    if (this.cachedTree && this.cachedVersion === this.instance.version) {
      return this.cachedTree;
    }

    const flatList = this.getFlatList().map(node => ({
      ...node,
      children: [] as any[]
    }));

    const rootNodes: any[] = [];
    const nodeMap = new Map<string, any>();

    for (const node of flatList) {
      nodeMap.set(node.id, node);
    }

    for (const node of flatList) {
      if (node.parent && nodeMap.has(node.parent)) {
        const parentNode = nodeMap.get(node.parent)!;
        parentNode.children.push(node);
        parentNode.children.sort((a: any, b: any) => a.order - b.order);
      } else {
        rootNodes.push(node);
      }
    }

    rootNodes.sort((a, b) => a.order - b.order);

    const deepFreeze = (arr: any[]): ReadonlyArray<NavigationNode> => {
      arr.forEach(node => {
        if (node.children) {
          deepFreeze(node.children);
        }
        Object.freeze(node);
      });
      return Object.freeze(arr);
    };

    this.cachedTree = deepFreeze(rootNodes);
    this.cachedVersion = this.instance.version;
    return this.cachedTree;
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
