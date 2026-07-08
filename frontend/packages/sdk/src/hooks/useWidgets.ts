import { useState, useEffect } from 'react';
import { WidgetRegistry, DashboardWidget } from '../index';

export const useWidgets = () => {
  const [widgets, setWidgets] = useState<ReadonlyArray<DashboardWidget>>(() => 
    WidgetRegistry.getWidgets()
  );

  useEffect(() => {
    // Subscribe directly to the registry updates
    const unsubscribe = WidgetRegistry.subscribe(() => {
      setWidgets(WidgetRegistry.getWidgets());
    });
    return unsubscribe;
  }, []);

  return widgets;
};
export default useWidgets;
