import type { Agreement } from '../types';

export class AgreementSchema {
  public static validate(agreement: Agreement): Agreement {
    if (!agreement.id?.trim()) {
      throw new Error('Agreement ID is required.');
    }

    if (!agreement.providerId?.trim()) {
      throw new Error('Provider ID is required.');
    }

    if (!agreement.title?.trim()) {
      throw new Error('Agreement title is required.');
    }

    if (!agreement.file) {
      throw new Error('Agreement file is required.');
    }

    if (!agreement.file.id?.trim()) {
      throw new Error('Agreement file ID is required.');
    }

    if (!agreement.file.filename?.trim()) {
      throw new Error('Agreement filename is required.');
    }

    if (!agreement.file.fileUrl?.trim()) {
      throw new Error('Agreement file URL is required.');
    }

    if (!agreement.expiresAt) {
      throw new Error('Agreement expiry date is required.');
    }

    return agreement;
  }

  public static validateMany(
    agreements: Agreement[],
  ): Agreement[] {
    return agreements.map(agreement => this.validate(agreement));
  }
}
export default AgreementSchema;
