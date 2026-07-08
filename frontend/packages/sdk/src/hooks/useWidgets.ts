import { useSyncExternalStore } from 'react';
import { WidgetRegistry, DashboardWidget } from '../index';

export const useWidgets = (): ReadonlyArray<DashboardWidget> => {
  return useSyncExternalStore(
    (listener) => WidgetRegistry.subscribe(listener),
    () => WidgetRegistry.getWidgets()
  );
};
export default useWidgets;
