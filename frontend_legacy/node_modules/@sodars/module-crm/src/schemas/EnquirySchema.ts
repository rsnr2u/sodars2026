import { Enquiry } from '../types';

export class EnquirySchema {
  public static validate(payload: any): Enquiry {
    if (!payload || typeof payload !== 'object') {
      throw new Error('[EnquirySchema] Enquiry payload must be an object.');
    }
    if (typeof payload.id !== 'string') {
      throw new Error('[EnquirySchema] Enquiry id is missing or invalid.');
    }
    if (typeof payload.name !== 'string' || payload.name.trim() === '') {
      throw new Error('[EnquirySchema] Enquiry name is missing or invalid.');
    }
    if (typeof payload.email !== 'string' || !payload.email.includes('@')) {
      throw new Error('[EnquirySchema] Enquiry email is missing or invalid.');
    }

    return {
      id: payload.id,
      name: payload.name.trim(),
      email: payload.email,
      phone: payload.phone || '',
      stage: payload.stage || 'New',
      source: payload.source || 'Website',
      campaignSource: payload.campaignSource,
      tags: Array.isArray(payload.tags) ? payload.tags : [],
      value: typeof payload.value === 'number' ? payload.value : 0,
      assignedTo: payload.assignedTo,
      activities: Array.isArray(payload.activities) ? payload.activities : [],
      followUps: Array.isArray(payload.followUps) ? payload.followUps : [],
      attachments: Array.isArray(payload.attachments) ? payload.attachments : [],
      createdAt: typeof payload.createdAt === 'number' ? payload.createdAt : Date.now()
    };
  }

  public static validateMany(list: any[]): Enquiry[] {
    if (!Array.isArray(list)) {
      throw new Error('[EnquirySchema] Enquiries payload must be an array.');
    }
    return list.map(item => this.validate(item));
  }
}
export default EnquirySchema;
