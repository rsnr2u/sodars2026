import { WidgetRegistry } from '@sodars/sdk';
import { CRM_MODULE_ID } from '../constants/module';

export const registerWidgets = (widgets: typeof WidgetRegistry): void => {
  const widgetList = [
    { id: 'crm.dashboard.followups', title: "Today's Callbacks & Follow-ups", w: 1, h: 1 },
    { id: 'crm.dashboard.pipeline', title: 'Pipeline Value Indicator', w: 1, h: 1 },
    { id: 'crm.dashboard.tasks', title: 'Personal Tasks checklist', w: 1, h: 1 },
    { id: 'crm.dashboard.leads', title: 'Open Enquiries Counter', w: 1, h: 1 },
    { id: 'crm.dashboard.team', title: 'Lead Sources Breakdown', w: 2, h: 1 },
    { id: 'crm.dashboard.performance', title: 'Team Conversion Ratios', w: 2, h: 1 }
  ];

  widgetList.forEach((w, idx) => {
    widgets.register({
      id: w.id,
      module: CRM_MODULE_ID,
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
