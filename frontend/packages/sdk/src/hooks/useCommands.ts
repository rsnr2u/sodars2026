import { useState, useEffect } from 'react';
import { CommandRegistry, AppCommand } from '../index';

export const useCommands = () => {
  const [commands, setCommands] = useState<ReadonlyArray<AppCommand>>(() => 
    CommandRegistry.getAll()
  );

  useEffect(() => {
    // Subscribe directly to the registry updates
    const unsubscribe = CommandRegistry.subscribe(() => {
      setCommands(CommandRegistry.getAll());
    });
    return unsubscribe;
  }, []);

  return commands;
};
export default useCommands;
