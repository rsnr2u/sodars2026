import type { Provider, ComplianceSummary } from '../types';

export class ComplianceCalculator {
  public static calculate(provider: Provider): ComplianceSummary {
    const now = Date.now();

    let documentsValid = 0;
    let documentsExpired = 0;
    provider.documents.forEach((doc) => {
      if (doc.expiresAt && doc.expiresAt < now) {
        documentsExpired++;
      } else {
        documentsValid++;
      }
    });

    let agreementsActive = 0;
    let agreementsExpired = 0;
    provider.agreements.forEach((agreement) => {
      if (agreement.expiresAt && agreement.expiresAt < now) {
        agreementsExpired++;
      } else {
        agreementsActive++;
      }
    });

    const gstVerified = !!provider.gstRegistration?.gstNumber;
    const bankVerified = !!provider.bankAccount?.accountNumber;

    let overallStatus: 'Compliant' | 'Pending' | 'Expired' = 'Compliant';

    if (documentsExpired > 0 || agreementsExpired > 0) {
      overallStatus = 'Expired';
    } else if (
      !gstVerified ||
      !bankVerified ||
      agreementsActive === 0 ||
      documentsValid === 0
    ) {
      overallStatus = 'Pending';
    }

    return {
      providerId: provider.id,
      documentsValid,
      documentsExpired,
      agreementsActive,
      agreementsExpired,
      gstVerified,
      bankVerified,
      overallStatus,
    };
  }
}
export default ComplianceCalculator;
