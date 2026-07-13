import { ComplianceService } from '../services/ComplianceService';
import { Document, Agreement, GSTRegistration, BankAccount, ComplianceSummary } from '../types';
import { useState, useEffect } from 'react';

export const useCompliance = (providerId?: string) => {
  const [documents, setDocuments] = useState<Document[]>([]);
  const [agreements, setAgreements] = useState<Agreement[]>([]);
  const [status, setStatus] = useState<ComplianceSummary | null>(null);
  const [isLoading, setIsLoading] = useState(!!providerId);
  const [error, setError] = useState<Error | null>(null);

  const refresh = async () => {
    if (!providerId) {
      setDocuments([]);
      setAgreements([]);
      setStatus(null);
      setIsLoading(false);
      return;
    }
    setIsLoading(true);
    try {
      const [docsRes, agrsRes, statusRes] = await Promise.all([
        ComplianceService.getDocuments(providerId),
        ComplianceService.getAgreements(providerId),
        ComplianceService.getComplianceStatus(providerId),
      ]);
      setDocuments(docsRes);
      setAgreements(agrsRes);
      setStatus(statusRes);
      setIsLoading(false);
    } catch (err) {
      setError(err instanceof Error ? err : new Error(String(err)));
      setIsLoading(false);
    }
  };

  const uploadDocument = async (document: Document) => {
    if (!providerId) throw new Error('No provider ID specified.');
    try {
      const created = await ComplianceService.uploadDocument(providerId, document);
      await refresh();
      return created;
    } catch (err) {
      throw err instanceof Error ? err : new Error(String(err));
    }
  };

  const updateDocument = async (documentId: string, changes: Partial<Document>) => {
    if (!providerId) throw new Error('No provider ID specified.');
    try {
      const updated = await ComplianceService.updateDocument(providerId, documentId, changes);
      await refresh();
      return updated;
    } catch (err) {
      throw err instanceof Error ? err : new Error(String(err));
    }
  };

  const renewDocument = async (documentId: string, newExpiryDate: number) => {
    if (!providerId) throw new Error('No provider ID specified.');
    try {
      const updated = await ComplianceService.renewDocument(providerId, documentId, newExpiryDate);
      await refresh();
      return updated;
    } catch (err) {
      throw err instanceof Error ? err : new Error(String(err));
    }
  };

  const expireDocument = async (documentId: string) => {
    if (!providerId) throw new Error('No provider ID specified.');
    try {
      await ComplianceService.expireDocument(providerId, documentId);
      await refresh();
    } catch (err) {
      throw err instanceof Error ? err : new Error(String(err));
    }
  };

  const deleteDocument = async (documentId: string) => {
    if (!providerId) throw new Error('No provider ID specified.');
    try {
      await ComplianceService.deleteDocument(providerId, documentId);
      await refresh();
    } catch (err) {
      throw err instanceof Error ? err : new Error(String(err));
    }
  };

  const createAgreement = async (agreement: Agreement) => {
    if (!providerId) throw new Error('No provider ID specified.');
    try {
      const created = await ComplianceService.createAgreement(providerId, agreement);
      await refresh();
      return created;
    } catch (err) {
      throw err instanceof Error ? err : new Error(String(err));
    }
  };

  const updateAgreement = async (agreementId: string, changes: Partial<Agreement>) => {
    if (!providerId) throw new Error('No provider ID specified.');
    try {
      const updated = await ComplianceService.updateAgreement(providerId, agreementId, changes);
      await refresh();
      return updated;
    } catch (err) {
      throw err instanceof Error ? err : new Error(String(err));
    }
  };

  const renewAgreement = async (agreementId: string, newExpiryDate: number) => {
    if (!providerId) throw new Error('No provider ID specified.');
    try {
      const updated = await ComplianceService.renewAgreement(providerId, agreementId, newExpiryDate);
      await refresh();
      return updated;
    } catch (err) {
      throw err instanceof Error ? err : new Error(String(err));
    }
  };

  const expireAgreement = async (agreementId: string) => {
    if (!providerId) throw new Error('No provider ID specified.');
    try {
      await ComplianceService.expireAgreement(providerId, agreementId);
      await refresh();
    } catch (err) {
      throw err instanceof Error ? err : new Error(String(err));
    }
  };

  const updateGSTRegistration = async (gst: GSTRegistration) => {
    if (!providerId) throw new Error('No provider ID specified.');
    try {
      const result = await ComplianceService.updateGSTRegistration(providerId, gst);
      await refresh();
      return result;
    } catch (err) {
      throw err instanceof Error ? err : new Error(String(err));
    }
  };

  const updateBankAccount = async (account: BankAccount) => {
    if (!providerId) throw new Error('No provider ID specified.');
    try {
      const result = await ComplianceService.updateBankAccount(providerId, account);
      await refresh();
      return result;
    } catch (err) {
      throw err instanceof Error ? err : new Error(String(err));
    }
  };

  useEffect(() => {
    refresh();
  }, [providerId]);

  return {
    documents,
    agreements,
    status,
    isLoading,
    error,
    refresh,
    uploadDocument,
    updateDocument,
    renewDocument,
    expireDocument,
    deleteDocument,
    createAgreement,
    updateAgreement,
    renewAgreement,
    expireAgreement,
    updateGSTRegistration,
    updateBankAccount,
  };
};
export default useCompliance;
