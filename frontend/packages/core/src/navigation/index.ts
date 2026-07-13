import { ModuleRegistry } from '../modules';

export interface NavigationItem {
  id: string;
  label: string;
  path: string;
  icon?: string;
  permission?: string;
}

export class NavigationRegistry {
  public static getSidebarItems(): NavigationItem[] {
    const items: NavigationItem[] = [];
    ModuleRegistry.getAll().forEach(mod => {
      if (mod.navigation) {
        mod.navigation.forEach(nav => {
          items.push({
            id: `${mod.id}-${nav.label.toLowerCase().replace(/\s+/g, '-')}`,
            label: nav.label,
            path: nav.path,
            icon: nav.icon,
            permission: nav.permission
          });
        });
      }
    });
    return items;
  }
}
