import { MockEnquiryRepository } from '../repositories/MockEnquiryRepository';
import { EnquirySchema } from '../schemas/EnquirySchema';
import { PipelineService } from './PipelineService';
import { Enquiry, SalesStage } from '../types';
import { CrmTelemetry } from '../telemetry/CrmTelemetry';

export class EnquiryService {
  private static repository = new MockEnquiryRepository();

  public static async getEnquiries(): Promise<Enquiry[]> {
    const raw = await this.repository.fetchEnquiries();
    return EnquirySchema.validateMany(raw);
  }

  public static async getEnquiry(id: string): Promise<Enquiry | null> {
    const raw = await this.repository.findEnquiry(id);
    if (!raw) return null;
    return EnquirySchema.validate(raw);
  }

  public static async updateStage(enquiryId: string, targetStage: SalesStage): Promise<Enquiry> {
    const enquiry = await this.getEnquiry(enquiryId);
    if (!enquiry) {
      throw new Error(`[EnquiryService] Enquiry with ID "${enquiryId}" not found.`);
    }

    // Validate workflow transition rules
    PipelineService.validateTransition(enquiry.stage, targetStage);

    const updated: Enquiry = {
      ...enquiry,
      stage: targetStage
    };

    const validated = EnquirySchema.validate(updated);
    const saved = await this.repository.saveEnquiry(validated);

    // Track telemetry conversions
    if (targetStage === 'Won') {
      CrmTelemetry.enquiryConverted(saved);
    } else {
      CrmTelemetry.pipelineChanged(saved, targetStage);
    }

    return saved;
  }

  public static async createEnquiry(payload: Partial<Enquiry>): Promise<Enquiry> {
    const newEnquiry: Enquiry = {
      id: payload.id || `enq_${Date.now()}`,
      name: payload.name || '',
      email: payload.email || '',
      phone: payload.phone || '',
      stage: 'New',
      source: payload.source || 'Website',
      campaignSource: payload.campaignSource,
      tags: payload.tags || [],
      value: payload.value || 0,
      activities: [],
      followUps: [],
      attachments: [],
      createdAt: Date.now()
    };

    const validated = EnquirySchema.validate(newEnquiry);
    const saved = await this.repository.saveEnquiry(validated);

    CrmTelemetry.enquiryCreated(saved);
    return saved;
  }
}
export default EnquiryService;
