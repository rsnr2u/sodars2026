import { useSyncExternalStore } from 'react';
import { WidgetRegistry, DashboardWidget } from '../index';
import { identity } from '@sodars/auth';

export const useWidgets = (): ReadonlyArray<DashboardWidget> => {
  const allWidgets = useSyncExternalStore(
    (listener) => WidgetRegistry.subscribe(listener),
    () => WidgetRegistry.getWidgets()
  );

  return allWidgets.filter(widget => {
    // 1. Gated by permissions
    if (widget.permission && !identity.can(widget.permission)) {
      return false;
    }

    // 2. Gated by active contextual predicates
    if (widget.enabled && !widget.enabled({ organizationId: 'org-999-id', branchId: 'brh-999-id' })) {
      return false;
    }

    return true;
  });
};
export default useWidgets;
