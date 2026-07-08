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
    const scored = list
      .map(cmd => {
        let score = 0;
        
        if (cmd.title.toLowerCase() === lowerQuery) {
          score += 150;
        } else if (cmd.title.toLowerCase().includes(lowerQuery)) {
          score += 100;
        }

        if (cmd.keywords.some(k => k.toLowerCase() === lowerQuery)) {
          score += 80;
        } else if (cmd.keywords.some(k => k.toLowerCase().includes(lowerQuery))) {
          score += 60;
        }

        if (cmd.aliases && cmd.aliases.some(a => a.toLowerCase() === lowerQuery)) {
          score += 50;
        } else if (cmd.aliases && cmd.aliases.some(a => a.toLowerCase().includes(lowerQuery))) {
          score += 40;
        }

        if (cmd.description && cmd.description.toLowerCase().includes(lowerQuery)) {
          score += 20;
        }

        return { cmd, score };
      })
      .filter(item => item.score > 0)
      .sort((a, b) => b.score - a.score || a.cmd.order - b.cmd.order)
      .map(item => item.cmd);

    return Object.freeze(scored);
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
