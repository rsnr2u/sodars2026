import { Agreement } from '../../types';

export interface IAgreementRepository {
  fetchAgreements(providerId?: string): Promise<Agreement[]>;
  saveAgreement(agreement: Agreement): Promise<Agreement>;
}
export default IAgreementRepository;
