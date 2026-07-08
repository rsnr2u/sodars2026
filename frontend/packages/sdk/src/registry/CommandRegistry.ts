import { BaseRegistry } from './BaseRegistry';
import { ModuleId } from '../index';
import { IconType } from '@sodars/icons';
import { CommandContext } from '../index';

export interface AppCommand {
  readonly id: string;
  readonly module: ModuleId;
  readonly title: string;
  readonly keywords: string[];
  readonly aliases?: string[];
  readonly description?: string;
  readonly group?: string;
  readonly icon?: IconType;
  readonly shortcut?: string;
  readonly permission?: string;
  readonly featureFlag?: string;
  readonly order: number;
  readonly execute: (context: CommandContext) => void | Promise<void>;
}

export class CommandRegistry extends BaseRegistry<AppCommand> {
  private static instance = new CommandRegistry();
  protected eventNamespace = 'command';

  private constructor() {
    super();
  }

  public static register(command: AppCommand): void {
    this.instance.register(command);
  }

  public static replace(command: AppCommand): void {
    this.instance.replace(command);
  }

  public static unregister(moduleName: ModuleId): void {
    this.instance.unregister(moduleName);
  }

  public static find(id: string): Readonly<AppCommand> | null {
    return this.instance.find(id);
  }

  public static getAll(): ReadonlyArray<AppCommand> {
    return this.instance.getAll();
  }

  public static search(query: string): ReadonlyArray<AppCommand> {
    const list = this.getAll();
    if (!query) return list;

    const lowerQuery = query.toLowerCase();
    return list.filter(cmd => 
      cmd.title.toLowerCase().includes(lowerQuery) ||
      cmd.keywords.some(k => k.toLowerCase().includes(lowerQuery)) ||
      (cmd.aliases && cmd.aliases.some(a => a.toLowerCase().includes(lowerQuery)))
    );
  }

  public static clear(): void {
    this.instance.clear();
  }

  public static getStats() {
    return this.instance.stats();
  }

  public static subscribe(listener: any) {
    return this.instance.subscribe(listener);
  }
}
export default CommandRegistry;
