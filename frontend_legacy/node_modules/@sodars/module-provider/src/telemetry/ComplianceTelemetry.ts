import { Severity, Telemetry } from '@sodars/observability';

const NAMESPACE = 'provider.compliance';

export class ComplianceTelemetry {
  public static trackDocumentUploaded(providerId: string, documentId: string): void {
    Telemetry.track(
      'command:executed',
      Severity.Info,
      { action: 'compliance.document_uploaded', providerId, documentId },
      NAMESPACE,
    );
  }

  public static trackDocumentExpired(providerId: string, documentId: string): void {
    Telemetry.track(
      'command:executed',
      Severity.Warning,
      { action: 'compliance.document_expired', providerId, documentId },
      NAMESPACE,
    );
  }

  public static trackAgreementCreated(providerId: string, agreementId: string): void {
    Telemetry.track(
      'command:executed',
      Severity.Info,
      { action: 'compliance.agreement_created', providerId, agreementId },
      NAMESPACE,
    );
  }

  public static trackAgreementExpired(providerId: string, agreementId: string): void {
    Telemetry.track(
      'command:executed',
      Severity.Warning,
      { action: 'compliance.agreement_expired', providerId, agreementId },
      NAMESPACE,
    );
  }

  public static trackBankAccountUpdated(providerId: string): void {
    Telemetry.track(
      'command:executed',
      Severity.Info,
      { action: 'compliance.bank_account_updated', providerId },
      NAMESPACE,
    );
  }

  public static trackGSTUpdated(providerId: string): void {
    Telemetry.track(
      'command:executed',
      Severity.Info,
      { action: 'compliance.gst_updated', providerId },
      NAMESPACE,
    );
  }
}
export default ComplianceTelemetry;
