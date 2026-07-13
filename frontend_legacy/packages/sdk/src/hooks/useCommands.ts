import { useSyncExternalStore } from 'react';
import { CommandRegistry, AppCommand } from '../index';

export const useCommands = (): ReadonlyArray<AppCommand> => {
  return useSyncExternalStore(
    (listener) => CommandRegistry.subscribe(listener),
    () => CommandRegistry.getAll()
  );
};
export default useCommands;
