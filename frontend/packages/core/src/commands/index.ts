import { ModuleRegistry } from '../modules';

export interface Command {
  phrase: string;
  action: () => void;
  shortcut?: string;
  moduleName?: string;
}

export class CommandRegistry {
  private static coreCommands: Command[] = [];

  public static registerCoreCommand(cmd: Command) {
    this.coreCommands.push(cmd);
  }

  public static getCommands(): Command[] {
    const commands = [...this.coreCommands];
    ModuleRegistry.getAll().forEach(mod => {
      if (mod.commands) {
        mod.commands.forEach(cmd => {
          commands.push({
            phrase: cmd.phrase,
            action: cmd.action,
            shortcut: cmd.shortcut,
            moduleName: mod.name
          });
        });
      }
    });
    return commands;
  }
}
