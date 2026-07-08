import { useState, useEffect } from 'react';
import { NavigationRegistry, NavigationNode } from '../index';

export const useNavigation = () => {
  const [nodes, setNodes] = useState<ReadonlyArray<NavigationNode>>(() => 
    NavigationRegistry.getTree()
  );

  useEffect(() => {
    // Subscribe directly to the registry updates
    const unsubscribe = NavigationRegistry.subscribe(() => {
      setNodes(NavigationRegistry.getTree());
    });
    return unsubscribe;
  }, []);

  return nodes;
};
export default useNavigation;
