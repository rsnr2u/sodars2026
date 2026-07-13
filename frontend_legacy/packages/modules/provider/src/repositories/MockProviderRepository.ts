import { MockRepository } from '@sodars/api';
import { TimelineEvent } from '@sodars/business-core';
import { ProviderStatus } from '../enums';
import { mockProviders } from '../mocks';
import {
  Agreement,
  BankAccount,
  Document,
  GSTRegistration,
  Provider,
  Verification,
} from '../types';
import { IProviderRepository } from './interfaces';

export class MockProviderRepository
  extends MockRepository<Provider>
  implements IProviderRepository {

  constructor() {
    super(mockProviders);
  }

  async findVerified(): Promise<Provider[]> {
    return this.filter(
      provider => provider.status === ProviderStatus.Verified,
    );
  }

  async findPendingVerification(): Promise<Provider[]> {
    return this.filter(
      provider =>
        provider.status === ProviderStatus.Pending ||
        provider.status === ProviderStatus.UnderReview,
    );
  }

  async search(query: string): Promise<Provider[]> {
    const term = query.toLowerCase();

    return this.filter(provider =>
      provider.name.toLowerCase().includes(term) ||
      provider.email.toLowerCase().includes(term)
    );
  }

  async exists(gstNumber: string): Promise<boolean> {
    return this.items.some(
      provider => provider.gstRegistration?.gstNumber === gstNumber,
    );
  }

  async getDocuments(providerId: string): Promise<Document[]> {
    return (await this.findById(providerId))?.documents ?? [];
  }

  async getAgreements(providerId: string): Promise<Agreement[]> {
    return (await this.findById(providerId))?.agreements ?? [];
  }

  async getBankAccounts(providerId: string): Promise<BankAccount[]> {
    const account = (await this.findById(providerId))?.bankAccount;

    return account ? [account] : [];
  }

  async getGSTRegistration(
    providerId: string,
  ): Promise<GSTRegistration | null> {
    return (await this.findById(providerId))?.gstRegistration ?? null;
  }

  async getVerification(
    providerId: string,
  ): Promise<Verification | null> {
    return (await this.findById(providerId))?.verifications.at(-1) ?? null;
  }

  async getTimeline(
    providerId: string,
  ): Promise<TimelineEvent[]> {
    return (await this.findById(providerId))?.timeline ?? [];
  }

  async verify(providerId: string): Promise<void> {
    await this.update(providerId, {
      status: ProviderStatus.Verified,
    });
  }

  async reject(
    providerId: string,
    reason: string,
  ): Promise<void> {
    await this.update(providerId, {
      status: ProviderStatus.Rejected,
      rejectionReason: reason,
    });
  }

  async suspend(providerId: string): Promise<void> {
    await this.update(providerId, {
      status: ProviderStatus.Suspended,
    });
  }

  async activate(providerId: string): Promise<void> {
    await this.update(providerId, {
      status: ProviderStatus.Verified,
    });
  }
}
export default MockProviderRepository;
