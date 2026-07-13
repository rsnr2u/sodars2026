export const CRM_EVENTS = {
  ENQUIRY_CREATED: 'crm.enquiry.created',
  ENQUIRY_ASSIGNED: 'crm.enquiry.assigned',
  ENQUIRY_UPDATED: 'crm.enquiry.updated',
  ENQUIRY_DELETED: 'crm.enquiry.deleted',
  ENQUIRY_CONVERTED: 'crm.enquiry.converted',
  CUSTOMER_CREATED: 'crm.customer.created',
  CUSTOMER_UPDATED: 'crm.customer.updated',
  QUOTATION_CREATED: 'crm.quotation.created',
  FOLLOWUP_COMPLETED: 'crm.followup.completed'
} as const;

export type CrmEventType = typeof CRM_EVENTS[keyof typeof CRM_EVENTS];
export default CRM_EVENTS;
