import { SodarsModule, ModuleSDK } from '@sodars/sdk';

export interface ModuleManifest {
  name: string;
  version: string;
  permissions: string[];
  dependencies: string[];
  widgets: boolean;
  menus: boolean;
}

export class AppRegistry {
  private static registeredModules: Map<string, ModuleManifest> = new Map();

  public static registerModule(manifest: ModuleManifest, module: SodarsModule): void {
    // Validate module dependencies
    for (const dep of manifest.dependencies) {
      if (!this.registeredModules.has(dep) && dep !== manifest.name) {
        console.warn(`[AppRegistry] Module "${manifest.name}" requires dependency "${dep}", which is not loaded yet.`);
      }
    }

    this.registeredModules.set(manifest.name, manifest);
    ModuleSDK.register(module);
    console.log(`[AppRegistry] Module "${manifest.name}" (v${manifest.version}) successfully registered.`);
  }

  public static getManifests(): ModuleManifest[] {
    return Array.from(this.registeredModules.values());
  }
}
