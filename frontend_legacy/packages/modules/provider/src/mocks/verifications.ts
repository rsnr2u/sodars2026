import { Verification } from '../types';
import { ProviderStatus, VerificationType, VerificationStepStatus } from '../enums';

export const mockVerifications: Verification[] = [
  {
    id: 'ver_1',
    providerId: 'prov_1',
    type: VerificationType.GST,
    status: ProviderStatus.Verified,
    steps: [
      { id: 'vstep_1', name: 'Document Audit', status: VerificationStepStatus.Passed, updatedAt: Date.now() }
    ],
    version: 1,
    isActive: true,
    createdAt: Date.now(),
    updatedAt: Date.now()
  }
];
export default mockVerifications;
