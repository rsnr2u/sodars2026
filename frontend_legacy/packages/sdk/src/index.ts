import { IconType } from '@sodars/icons';
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

import { NavigationRegistry } from './registry/NavigationRegistry';
export { NavigationRegistry };

// 5. Registry Manager Facade
import { RegistryManager, registryManager } from './registry/RegistryManager';
export { RegistryManager, registryManager };

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
export * from './registry/PermissionRegistry';
export * from './registry/RouteRegistry';
export * from './adapters/QueryClientAdapter';
export * from './adapters/RouterAdapter';
export * from './adapters/RequestContextAdapter';
export * from './adapters/TelemetryAdapter';
export * from './hooks/useNavigation';
export * from './hooks/useWidgets';
export * from './hooks/useCommands';
export * from './module/ModuleManager';
export * from './module/BusinessModule';
