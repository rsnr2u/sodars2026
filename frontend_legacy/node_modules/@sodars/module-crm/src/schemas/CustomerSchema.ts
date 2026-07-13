import { Customer } from '../types';

export class CustomerSchema {
  public static validate(payload: any): Customer {
    if (!payload || typeof payload !== 'object') {
      throw new Error('[CustomerSchema] Customer payload must be an object.');
    }
    if (typeof payload.id !== 'string') {
      throw new Error('[CustomerSchema] Customer id is missing or invalid.');
    }
    if (typeof payload.companyName !== 'string' || payload.companyName.trim() === '') {
      throw new Error('[CustomerSchema] Customer companyName is missing or invalid.');
    }
    if (typeof payload.email !== 'string' || !payload.email.includes('@')) {
      throw new Error('[CustomerSchema] Customer email is missing or invalid.');
    }

    return {
      id: payload.id,
      companyName: payload.companyName.trim(),
      email: payload.email,
      phone: payload.phone || '',
      contacts: Array.isArray(payload.contacts) ? payload.contacts : [],
      addresses: Array.isArray(payload.addresses) ? payload.addresses : [],
      timeline: Array.isArray(payload.timeline) ? payload.timeline : [],
      createdAt: typeof payload.createdAt === 'number' ? payload.createdAt : Date.now()
    };
  }

  public static validateMany(list: any[]): Customer[] {
    if (!Array.isArray(list)) {
      throw new Error('[CustomerSchema] Customers payload must be an array.');
    }
    return list.map(item => this.validate(item));
  }
}
export default CustomerSchema;
