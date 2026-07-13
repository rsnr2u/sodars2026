export interface ModuleManifest {
  id: string;
  name: string;
  icon?: string;
  routes: Array<{ path: string; component: any }>;
  permissions?: string[];
  navigation?: Array<{ label: string; path: string; icon?: string; permission?: string }>;
  widgets?: Array<{ id: string; title: string; category: string }>;
  commands?: Array<{ phrase: string; action: () => void; shortcut?: string }>;
}

export class ModuleRegistry {
  private static modules = new Map<string, ModuleManifest>();

  public static register(manifest: ModuleManifest) {
    this.modules.set(manifest.id, manifest);
  }

  public static get(id: string): ModuleManifest | undefined {
    return this.modules.get(id);
  }

  public static getAll(): ModuleManifest[] {
    return Array.from(this.modules.values());
  }
}
