import { useSyncExternalStore } from 'react';
import { NavigationRegistry, NavigationNode } from '../index';

export const useNavigation = (): ReadonlyArray<NavigationNode> => {
  return useSyncExternalStore(
    (listener) => NavigationRegistry.subscribe(listener),
    () => NavigationRegistry.getTree()
  );
};
export default useNavigation;
