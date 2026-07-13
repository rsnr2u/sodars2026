import { BaseRepository } from '@sodars/api';
import { TimelineEvent } from '@sodars/business-core';
import {
  Provider,
  Document,
  Agreement,
  BankAccount,
  GSTRegistration,
  Verification,
} from '../../types';

export interface IProviderRepository extends BaseRepository<Provider> {
  // Provider Queries
  findVerified(): Promise<Provider[]>;
  findPendingVerification(): Promise<Provider[]>;
  search(query: string): Promise<Provider[]>;
  exists(gstNumber: string): Promise<boolean>;

  // Aggregate Children
  getDocuments(providerId: string): Promise<Document[]>;
  getAgreements(providerId: string): Promise<Agreement[]>;
  getBankAccounts(providerId: string): Promise<BankAccount[]>;
  getGSTRegistration(providerId: string): Promise<GSTRegistration | null>;
  getVerification(providerId: string): Promise<Verification | null>;
  getTimeline(providerId: string): Promise<TimelineEvent[]>;

  // Business Operations
  verify(providerId: string): Promise<void>;
  reject(providerId: string, reason: string): Promise<void>;
  suspend(providerId: string): Promise<void>;
  activate(providerId: string): Promise<void>;
}
export default IProviderRepository;
