import { Verification } from '../../types';

export interface IVerificationRepository {
  fetchVerifications(providerId?: string): Promise<Verification[]>;
  saveVerification(verification: Verification): Promise<Verification>;
}
export default IVerificationRepository;
