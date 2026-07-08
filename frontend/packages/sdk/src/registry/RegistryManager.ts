import { NavigationRegistry } from '../index';
import { WidgetRegistry } from './WidgetRegistry';
import { CommandRegistry } from './CommandRegistry';

export class RegistryManager {
  public readonly navigation = NavigationRegistry;
  public readonly widgets = WidgetRegistry;
  public readonly commands = CommandRegistry;
}

export const registryManager = new RegistryManager();
export default registryManager;
