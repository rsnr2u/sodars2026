import { IconType } from '@sodars/icons';
import { BaseRegistry } from './registry/BaseRegistry';
import { Config } from '@sodars/config';
import { EventBus } from '@sodars/events';
import { IdentityFacade } from '@sodars/auth';
import { RequestContextAdapter } from './adapters/RequestContextAdapter';
import { TelemetryAdapter } from './adapters/TelemetryAdapter';
import { QueryClientAdapter } from './adapters/QueryClientAdapter';
import { RouterAdapter } from './adapters/RouterAdapter';

// 1. Module ID Types
export type ModuleId =
  | 'dashboard'
  | 'crm'
  | 'campaign'
  | 'inventory'
  | 'provider'
  | 'wallet'
  | 'finance'
  | 'transport'
  | 'operations'
  | 'analytics'
  | 'audit'
  | 'iam'
  | 'settings';

export interface RegistryItem {
  readonly id: string;
  readonly module: ModuleId;
  readonly order: number;
}

// 2. Context Types
export interface BootstrapContext {
  readonly config: typeof Config;
  readonly eventBus: typeof EventBus;
  readonly queryClient: QueryClientAdapter;
  readonly identity: IdentityFacade;
  readonly registry: RegistryManager;
  readonly requestContext: RequestContextAdapter;
  readonly telemetry: TelemetryAdapter;
}

export interface CommandContext {
  readonly router: RouterAdapter;
  readonly identity: IdentityFacade;
  readonly queryClient: QueryClientAdapter;
  readonly registry: RegistryManager;
}

// 3. Navigation Node Types
export interface BadgeDefinition {
  readonly value: string | number;
  readonly variant: 'primary' | 'success' | 'warning' | 'danger' | 'info';
  readonly pulse?: boolean;
  readonly tooltip?: string;
}

export interface NavigationNode {
  readonly id: string;
  readonly module: ModuleId;
  readonly title: string;
  readonly route?: string;
  readonly icon?: IconType;
  readonly parent?: string;
  readonly order: number;
  readonly permission?: string;
  readonly featureFlag?: string;
  readonly badge?: Readonly<BadgeDefinition>;
  readonly hidden?: boolean;
  readonly disabled?: boolean;
  readonly external?: boolean;
  readonly target?: "_self" | "_blank";
  readonly children?: ReadonlyArray<NavigationNode>;
}

// 4. Navigation Registry inheriting BaseRegistry
export class NavigationRegistry extends BaseRegistry<NavigationNode> {
  private static instance = new NavigationRegistry();
  protected eventNamespace = 'navigation';

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

    return deepFreeze(rootNodes);
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

// 5. Registry Manager Facade
import { WidgetRegistry } from './registry/WidgetRegistry';
import { CommandRegistry } from './registry/CommandRegistry';

export class RegistryManager {
  public readonly navigation = NavigationRegistry;
  public readonly widgets = WidgetRegistry;
  public readonly commands = CommandRegistry;
}

export const registryManager = new RegistryManager();

// 6. Pluggable Module Interface
export interface SodarsModule {
  readonly id: ModuleId;
  readonly version: string;
  readonly displayName: string;
  readonly description?: string;
  readonly author?: string;
  readonly category?: "core" | "business" | "integration" | "experimental";
  readonly enabledByDefault?: boolean;
  readonly dependencies?: ModuleId[];
  readonly optional?: ModuleId[];
  bootstrap(context: BootstrapContext): Promise<void>;
  register(registry: RegistryManager): void;
  start(): Promise<void>;
  stop(): Promise<void>;
  unregister(): void;
  shutdown(): Promise<void>;
}

// Re-exports
export * from './registry/BaseRegistry';
export * from './registry/Registry';
export * from './registry/RegistryManager';
export * from './registry/WidgetRegistry';
export * from './registry/CommandRegistry';
export * from './adapters/QueryClientAdapter';
export * from './adapters/RouterAdapter';
export * from './adapters/RequestContextAdapter';
export * from './adapters/TelemetryAdapter';
export * from './hooks/useNavigation';
export * from './hooks/useWidgets';
export * from './hooks/useCommands';
