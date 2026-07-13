import { WidgetRegistry } from '@sodars/sdk';
import { IAM_MODULE_ID } from '../constants/module';

export const registerWidgets = (widgets: typeof WidgetRegistry): void => {
  const widgetList = [
    { id: 'widget.iam.total_users', title: 'Total Registered Accounts', w: 1, h: 1, type: 'metric' },
    { id: 'widget.iam.active_users', title: 'Active Accounts Monitor', w: 1, h: 1, type: 'metric' },
    { id: 'widget.iam.locked_users', title: 'Locked Security Accounts', w: 1, h: 1, type: 'metric' },
    { id: 'widget.iam.active_sessions', title: 'Active Login Sessions Count', w: 2, h: 1, type: 'chart' }
  ];

  widgetList.forEach((w, idx) => {
    widgets.register({
      id: w.id,
      module: IAM_MODULE_ID,
      title: w.title,
      width: w.w as any,
      height: w.h,
      order: idx + 1,
      // Default empty component builder for dashboard mounting
      component: () => null
    });
  });
};
export default registerWidgets;
