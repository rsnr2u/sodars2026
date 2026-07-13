import { SodarsModule, ModuleId } from '../index';

export type ModuleCategory = 'core' | 'business' | 'integration' | 'experimental';

export interface ModuleManifest {
  readonly id: ModuleId;
  readonly version: string;
  readonly displayName: string;
  readonly description?: string;
  readonly category: ModuleCategory;
  readonly dependencies: ModuleId[];
  readonly optionalDependencies?: ModuleId[];
  readonly permissions?: string[];
  readonly featureFlags?: string[];
  readonly sdkVersion: string;
  readonly minimumPlatformVersion?: string;
}

export enum ModuleState {
  Discovered = 'Discovered',
  Installed = 'Installed',
  Registered = 'Registered',
  Started = 'Started',
  Running = 'Running',
  Stopped = 'Stopped',
  Uninstalled = 'Uninstalled'
}

export interface BusinessModule<TContext = any, TServices = any> extends SodarsModule {
  readonly manifest: ModuleManifest;
  readonly context: TContext;
  readonly services: TServices;
}
