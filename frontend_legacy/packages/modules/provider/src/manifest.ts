import { BusinessModule, ModuleManifest, BootstrapContext, RegistryManager } from '@sodars/sdk';
import { Telemetry, Severity } from '@sodars/observability';
import { PROVIDER_MODULE_ID, PROVIDER_VERSION } from './constants/module';
import { registerNavigation } from './navigation/registerNavigation';
import { registerCommands } from './commands/registerCommands';
import { registerWidgets } from './widgets/registerWidgets';
import { registerPermissions } from './permissions/registerPermissions';
import { registerRoutes } from './routes/registerRoutes';

export class ProviderModule implements BusinessModule<any, any> {
  readonly id = PROVIDER_MODULE_ID;
  readonly version = PROVIDER_VERSION;
  readonly displayName = 'Provider Management';
  readonly description = 'Enterprise master provider profile and branch coordinates directory module';
  readonly category = 'business';
  
  readonly manifest: ModuleManifest = {
    id: PROVIDER_MODULE_ID,
    version: PROVIDER_VERSION,
    displayName: 'Provider Management',
    description: 'Enterprise master provider profile and branch coordinates directory module',
    category: 'business',
    dependencies: [],
    permissions: [
      'provider.dashboard.view',
      'provider.providers.view',
      'provider.providers.create',
      'provider.branches.view',
      'provider.staff.view',
      'provider.documents.view'
    ],
    sdkVersion: '1.0.0'
  };

  readonly context: any = {};
  readonly services: any = {};

  public async bootstrap(_context: BootstrapContext): Promise<void> {
    Telemetry.track('module:bootstrapped', Severity.Info, { moduleId: this.id });
  }

  public register(registry: RegistryManager): void {
    registerNavigation(registry.navigation);
    registerCommands(registry.commands);
    registerWidgets(registry.widgets);
    registerPermissions(registry.permissions);
    registerRoutes(registry.routes);
  }

  public async start(): Promise<void> {
    Telemetry.track('module:started', Severity.Info, { moduleId: this.id });
  }

  public async stop(): Promise<void> {
    Telemetry.track('module:stopped', Severity.Info, { moduleId: this.id });
  }

  public unregister(): void {
    // Automatic cleanup managed by SDK registries
  }

  public async shutdown(): Promise<void> {
    Telemetry.track('module:shutdown', Severity.Info, { moduleId: this.id });
  }
}
export default ProviderModule;
