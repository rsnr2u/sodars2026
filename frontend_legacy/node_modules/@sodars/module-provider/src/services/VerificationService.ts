import { providerRepositories } from '../repositories';
import { VerificationSchema } from '../schemas';
import { VerificationTelemetry } from '../telemetry';
import { ProviderStatus, VerificationStepStatus } from '../enums';
import type { VerificationType } from '../enums';
import type { Verification } from '../types';

export class VerificationService {
  private static readonly providerRepository = providerRepositories.provider;

  public static async getVerification(
    providerId: string
  ): Promise<Verification | null> {
    const provider = await this.providerRepository.findById(providerId);
    if (!provider) {
      return null;
    }
    return provider.verifications.at(-1) ?? null;
  }

  public static async startVerification(
    providerId: string,
    type: VerificationType
  ): Promise<Verification> {
    const provider = await this.providerRepository.findById(providerId);
    if (!provider) {
      throw new Error('Provider not found.');
    }

    // State machine check: cannot start if already Under Review or Verified
    if (
      provider.status === ProviderStatus.UnderReview ||
      provider.status === ProviderStatus.Verified
    ) {
      throw new Error(
        `Cannot start verification for provider in ${provider.status} status.`
      );
    }

    const verificationId = `v-${Date.now()}`;
    const newVerification: Verification = {
      id: verificationId,
      providerId,
      type,
      status: ProviderStatus.UnderReview,
      steps: [
        {
          id: 'gst',
          name: 'GST Verification',
          status: VerificationStepStatus.Pending,
          updatedAt: Date.now(),
        },
        {
          id: 'bank',
          name: 'Bank Account Verification',
          status: VerificationStepStatus.Pending,
          updatedAt: Date.now(),
        },
        {
          id: 'documents',
          name: 'Documents Verification',
          status: VerificationStepStatus.Pending,
          updatedAt: Date.now(),
        },
      ],
      comments: '',
      createdAt: Date.now(),
      updatedAt: Date.now(),
      isActive: true,
      version: 1,
    };

    const validated = VerificationSchema.validate(newVerification);

    // Update aggregate
    provider.verifications.push(validated);
    provider.status = ProviderStatus.UnderReview;
    provider.updatedAt = Date.now();

    await this.providerRepository.update(providerId, {
      verifications: provider.verifications,
      status: provider.status,
      updatedAt: provider.updatedAt,
    });

    VerificationTelemetry.trackStarted(providerId, validated.id);

    return validated;
  }

  public static async updateStepStatus(
    providerId: string,
    verificationId: string,
    stepId: string,
    status: VerificationStepStatus,
    notes?: string
  ): Promise<Verification> {
    const provider = await this.providerRepository.findById(providerId);
    if (!provider) {
      throw new Error('Provider not found.');
    }

    const verification = provider.verifications.find(
      (v) => v.id === verificationId
    );
    if (!verification) {
      throw new Error('Verification not found.');
    }

    // State machine check: verification itself must be Under Review
    if (verification.status !== ProviderStatus.UnderReview) {
      throw new Error(
        'Cannot update step status for a verification that is not Under Review.'
      );
    }

    const step = verification.steps.find((s) => s.id === stepId);
    if (!step) {
      throw new Error('Verification step not found.');
    }

    // Step state transition rules: only Pending -> Passed or Pending -> Failed
    if (
      step.status === VerificationStepStatus.Passed ||
      step.status === VerificationStepStatus.Failed
    ) {
      throw new Error(
        `Verification step is already in ${step.status} state and cannot be modified.`
      );
    }

    if (status === VerificationStepStatus.Pending) {
      throw new Error('Cannot revert a step to Pending status.');
    }

    // Apply mutations
    step.status = status;
    if (notes !== undefined) {
      step.notes = notes;
    }
    step.updatedAt = Date.now();
    verification.updatedAt = Date.now();
    provider.updatedAt = Date.now();

    // Validate the complete verification before saving
    const validated = VerificationSchema.validate(verification);

    await this.providerRepository.update(providerId, {
      verifications: provider.verifications,
      updatedAt: provider.updatedAt,
    });

    VerificationTelemetry.trackStepUpdated(
      providerId,
      verificationId,
      stepId,
      status
    );

    return validated;
  }

  public static async approveVerification(
    providerId: string,
    verificationId: string
  ): Promise<void> {
    const provider = await this.providerRepository.findById(providerId);
    if (!provider) {
      throw new Error('Provider not found.');
    }

    const verification = provider.verifications.find(
      (v) => v.id === verificationId
    );
    if (!verification) {
      throw new Error('Verification not found.');
    }

    if (verification.status !== ProviderStatus.UnderReview) {
      throw new Error('Verification is not Under Review.');
    }

    // Ensure all steps have passed
    const allPassed = verification.steps.every(
      (s) => s.status === VerificationStepStatus.Passed
    );
    if (!allPassed) {
      throw new Error('Cannot approve verification until all steps have passed.');
    }

    verification.status = ProviderStatus.Verified;
    verification.updatedAt = Date.now();
    provider.status = ProviderStatus.Verified;
    provider.rejectionReason = undefined;
    provider.updatedAt = Date.now();

    // Validate
    VerificationSchema.validate(verification);

    await this.providerRepository.update(providerId, {
      status: ProviderStatus.Verified,
      rejectionReason: undefined,
      verifications: provider.verifications,
      updatedAt: provider.updatedAt,
    });

    VerificationTelemetry.trackApproved(providerId, verificationId);
  }

  public static async rejectVerification(
    providerId: string,
    verificationId: string,
    reason: string
  ): Promise<void> {
    if (!reason?.trim()) {
      throw new Error('Rejection reason is required.');
    }

    const provider = await this.providerRepository.findById(providerId);
    if (!provider) {
      throw new Error('Provider not found.');
    }

    const verification = provider.verifications.find(
      (v) => v.id === verificationId
    );
    if (!verification) {
      throw new Error('Verification not found.');
    }

    if (verification.status !== ProviderStatus.UnderReview) {
      throw new Error('Verification is not Under Review.');
    }

    verification.status = ProviderStatus.Rejected;
    verification.comments = reason;
    verification.updatedAt = Date.now();
    provider.status = ProviderStatus.Rejected;
    provider.rejectionReason = reason;
    provider.updatedAt = Date.now();

    // Validate
    VerificationSchema.validate(verification);

    await this.providerRepository.update(providerId, {
      status: ProviderStatus.Rejected,
      rejectionReason: reason,
      verifications: provider.verifications,
      updatedAt: provider.updatedAt,
    });

    VerificationTelemetry.trackRejected(providerId, verificationId, reason);
  }

  public static async restartVerification(
    providerId: string,
    verificationId: string
  ): Promise<Verification> {
    const provider = await this.providerRepository.findById(providerId);
    if (!provider) {
      throw new Error('Provider not found.');
    }

    const verification = provider.verifications.find(
      (v) => v.id === verificationId
    );
    if (!verification) {
      throw new Error('Verification not found.');
    }

    if (verification.status !== ProviderStatus.Rejected) {
      throw new Error(
        'Verification can only be restarted if it has been Rejected.'
      );
    }

    // Reset steps
    verification.steps.forEach((step) => {
      step.status = VerificationStepStatus.Pending;
      step.notes = undefined;
      step.updatedAt = Date.now();
    });

    verification.status = ProviderStatus.UnderReview;
    verification.comments = '';
    verification.updatedAt = Date.now();
    provider.status = ProviderStatus.UnderReview;
    provider.rejectionReason = undefined;
    provider.updatedAt = Date.now();

    // Validate
    const validated = VerificationSchema.validate(verification);

    await this.providerRepository.update(providerId, {
      status: ProviderStatus.UnderReview,
      rejectionReason: undefined,
      verifications: provider.verifications,
      updatedAt: provider.updatedAt,
    });

    VerificationTelemetry.trackRestarted(providerId, verificationId);

    return validated;
  }
}
export default VerificationService;
