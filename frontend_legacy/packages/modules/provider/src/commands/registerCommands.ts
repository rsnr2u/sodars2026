import { CommandRegistry } from '@sodars/sdk';
import { PROVIDER_MODULE_ID } from '../constants/module';

export const registerCommands = (commands: typeof CommandRegistry): void => {
  const commandsList = [
    { id: 'cmd.provider.list', title: 'Go to Providers List', route: '/providers', keywords: ['provider list', 'all providers', 'lookup provider'] },
    { id: 'cmd.provider.new', title: 'Register New Provider', route: '/providers/new', keywords: ['add provider', 'new provider', 'register provider'] }
  ];

  for (const cmd of commandsList) {
    commands.register({
      id: cmd.id,
      module: PROVIDER_MODULE_ID,
      title: cmd.title,
      keywords: cmd.keywords,
      group: 'Provider Management',
      order: 30,
      execute: (ctx) => {
        ctx.router.navigate(cmd.route);
      }
    });
  }
};
export default registerCommands;
