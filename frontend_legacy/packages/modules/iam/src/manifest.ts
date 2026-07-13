import { BusinessModule, ModuleManifest, BootstrapContext, RegistryManager } from '@sodars/sdk';
import { Telemetry, Severity } from '@sodars/observability';
import { 
  IAM_MODULE_ID, 
  IAM_VERSION, 
  IAM_DISPLAY_NAME, 
  IAM_DESCRIPTION 
} from './constants/module';
import { registerNavigation } from './navigation/registerNavigation';
import { registerCommands } from './commands/registerCommands';
import { registerWidgets } from './widgets/registerWidgets';
import { registerPermissions } from './permissions/registerPermissions';
import { registerRoutes } from './routes/registerRoutes';
import { UserService } from './services/UserService';
import { UserRepository } from './repositories/UserRepository';
import { UserTelemetry } from './telemetry/UserTelemetry';

export interface IamModuleContext {
  readonly repository: typeof UserRepository;
  readonly telemetry: typeof UserTelemetry;
}

export interface IamModuleServices {
  readonly users: typeof UserService;
}

export class IamModule implements BusinessModule<IamModuleContext, IamModuleServices> {
  readonly id = IAM_MODULE_ID;
  readonly version = IAM_VERSION;
  readonly displayName = IAM_DISPLAY_NAME;
  readonly description = IAM_DESCRIPTION;
  readonly category = 'core';
  
  readonly manifest: ModuleManifest = {
    id: IAM_MODULE_ID,
    version: IAM_VERSION,
    displayName: IAM_DISPLAY_NAME,
    description: IAM_DESCRIPTION,
    category: 'core',
    dependencies: [],
    permissions: ['iam.users.view', 'iam.users.create', 'iam.roles.view'],
    sdkVersion: '1.0.0'
  };

  readonly context: IamModuleContext = {
    repository: UserRepository,
    telemetry: UserTelemetry
  };
  
  readonly services: IamModuleServices = {
    users: UserService
  };

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
export default IamModule;
