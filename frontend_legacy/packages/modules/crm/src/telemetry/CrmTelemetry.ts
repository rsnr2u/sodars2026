import { Telemetry, Severity } from '@sodars/observability';
import { Enquiry, Customer } from '../types';

export class CrmTelemetry {
  public static enquiryCreated(enquiry: Enquiry): void {
    Telemetry.track('command:executed', Severity.Info, { action: 'crm.enquiry.created', enquiryId: enquiry.id }, 'crm');
  }

  public static enquiryAssigned(enquiry: Enquiry, userId: string): void {
    Telemetry.track('command:executed', Severity.Info, { action: 'crm.enquiry.assigned', enquiryId: enquiry.id, userId }, 'crm');
  }

  public static enquiryConverted(enquiry: Enquiry): void {
    Telemetry.track('command:executed', Severity.Info, { action: 'crm.enquiry.converted', enquiryId: enquiry.id }, 'crm');
  }

  public static customerCreated(customer: Customer): void {
    Telemetry.track('command:executed', Severity.Info, { action: 'crm.customer.created', customerId: customer.id }, 'crm');
  }

  public static pipelineChanged(enquiry: Enquiry, targetStage: string): void {
    Telemetry.track('command:executed', Severity.Warning, { action: 'crm.pipeline.changed', enquiryId: enquiry.id, targetStage }, 'crm');
  }
}
export default CrmTelemetry;
