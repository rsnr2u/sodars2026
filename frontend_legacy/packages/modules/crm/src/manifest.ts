import { BusinessModule, ModuleManifest, BootstrapContext, RegistryManager } from '@sodars/sdk';
import { Telemetry, Severity } from '@sodars/observability';
import { 
  CRM_MODULE_ID, 
  CRM_VERSION, 
  CRM_DISPLAY_NAME, 
  CRM_DESCRIPTION 
} from './constants/module';
import { registerNavigation } from './navigation/registerNavigation';
import { registerCommands } from './commands/registerCommands';
import { registerWidgets } from './widgets/registerWidgets';
import { registerPermissions } from './permissions/registerPermissions';
import { registerRoutes } from './routes/registerRoutes';

export class CrmModule implements BusinessModule<any, any> {
  readonly id = CRM_MODULE_ID;
  readonly version = CRM_VERSION;
  readonly displayName = CRM_DISPLAY_NAME;
  readonly description = CRM_DESCRIPTION;
  readonly category = 'business';
  
  readonly manifest: ModuleManifest = {
    id: CRM_MODULE_ID,
    version: CRM_VERSION,
    displayName: CRM_DISPLAY_NAME,
    description: CRM_DESCRIPTION,
    category: 'business',
    dependencies: [],
    permissions: [
      'crm.dashboard.view',
      'crm.enquiries.view',
      'crm.enquiries.create',
      'crm.customers.view'
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
export default CrmModule;
