import { NavigationRegistry } from '../index';
import { WidgetRegistry } from './WidgetRegistry';
import { CommandRegistry } from './CommandRegistry';
import { PermissionRegistry } from './PermissionRegistry';
import { RouteRegistry } from './RouteRegistry';

export class RegistryManager {
  public readonly navigation = NavigationRegistry;
  public readonly widgets = WidgetRegistry;
  public readonly commands = CommandRegistry;
  public readonly permissions = PermissionRegistry;
  public readonly routes = RouteRegistry;
}

export const registryManager = new RegistryManager();
export default registryManager;
