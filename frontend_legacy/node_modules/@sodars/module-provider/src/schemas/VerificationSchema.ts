import type { Verification } from '../types';

export class VerificationSchema {
  public static validate(verification: Verification): Verification {
    if (!verification.id?.trim()) {
      throw new Error('Verification ID is required.');
    }

    if (!verification.providerId?.trim()) {
      throw new Error('Provider ID is required.');
    }

    if (!verification.type) {
      throw new Error('Verification type is required.');
    }

    if (!verification.status) {
      throw new Error('Verification status is required.');
    }

    if (!verification.steps || verification.steps.length === 0) {
      throw new Error('At least one verification step is required.');
    }

    verification.steps.forEach((step, index) => {
      if (!step.id?.trim()) {
        throw new Error(`Verification step #${index + 1} requires an ID.`);
      }

      if (!step.name?.trim()) {
        throw new Error(`Verification step #${index + 1} requires a name.`);
      }

      if (!step.status) {
        throw new Error(`Verification step #${index + 1} requires a status.`);
      }
    });

    return verification;
  }

  public static validateMany(
    verifications: Verification[],
  ): Verification[] {
    return verifications.map(verification => this.validate(verification));
  }
}
export default VerificationSchema;
