import { SodarsModule, BootstrapContext, ModuleId } from '../index';

export class ModuleManager {
  private static modules: Map<ModuleId, SodarsModule> = new Map();
  private static bootstrappedModules: ModuleId[] = [];

  public static register(module: SodarsModule): void {
    if (this.modules.has(module.id)) {
      console.warn(`[ModuleManager] Module "${module.id}" is already registered.`);
      return;
    }
    this.modules.set(module.id, module);
  }

  public static getModules(): ReadonlyArray<SodarsModule> {
    return Object.freeze(Array.from(this.modules.values()));
  }

  public static async bootstrapAll(context: BootstrapContext): Promise<void> {
    console.log('[ModuleManager] Initiating module dependency graph topological sorting...');
    const sorted = this.topologicalSort();

    for (const mod of sorted) {
      console.log(`[ModuleManager] Bootstrapping module: ${mod.displayName} (v${mod.version})`);
      await mod.bootstrap(context);
      mod.register(context.registry);
      this.bootstrappedModules.push(mod.id);
    }
  }

  public static async startAll(): Promise<void> {
    for (const id of this.bootstrappedModules) {
      const mod = this.modules.get(id);
      if (mod) {
        console.log(`[ModuleManager] Starting module: ${mod.displayName}`);
        await mod.start();
      }
    }
  }

  public static async stopAll(): Promise<void> {
    // Stop modules in reverse boot order
    const reversed = [...this.bootstrappedModules].reverse();
    for (const id of reversed) {
      const mod = this.modules.get(id);
      if (mod) {
        console.log(`[ModuleManager] Stopping module: ${mod.displayName}`);
        await mod.stop();
      }
    }
  }

  public static async shutdownAll(): Promise<void> {
    const reversed = [...this.bootstrappedModules].reverse();
    for (const id of reversed) {
      const mod = this.modules.get(id);
      if (mod) {
        console.log(`[ModuleManager] Shutting down module: ${mod.displayName}`);
        await mod.shutdown();
        mod.unregister();
      }
    }
    this.bootstrappedModules = [];
  }

  private static topologicalSort(): SodarsModule[] {
    const list = Array.from(this.modules.values());
    const visited = new Set<ModuleId>();
    const temp = new Set<ModuleId>();
    const order: SodarsModule[] = [];

    const visit = (mod: SodarsModule) => {
      if (visited.has(mod.id)) return;
      if (temp.has(mod.id)) {
        throw new Error(`[ModuleManager] Circular dependency detected in module "${mod.id}"!`);
      }

      temp.add(mod.id);

      if (mod.dependencies) {
        for (const depId of mod.dependencies) {
          const depMod = this.modules.get(depId);
          if (depMod) {
            visit(depMod);
          } else {
            console.warn(`[ModuleManager] Missing dependency "${depId}" required by module "${mod.id}"`);
          }
        }
      }

      temp.delete(mod.id);
      visited.add(mod.id);
      order.push(mod);
    };

    for (const mod of list) {
      visit(mod);
    }

    return order;
  }
}
export default ModuleManager;
