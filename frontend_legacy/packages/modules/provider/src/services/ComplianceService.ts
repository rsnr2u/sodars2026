import { providerRepositories } from '../repositories';
import { DocumentSchema, AgreementSchema } from '../schemas';
import { ComplianceTelemetry } from '../telemetry';
import { ComplianceCalculator } from '../calculators/ComplianceCalculator';
import type {
  Document,
  Agreement,
  BankAccount,
  GSTRegistration,
  ComplianceSummary,
} from '../types';

export class ComplianceService {
  private static readonly providerRepository = providerRepositories.provider;

  // Private EntityBase Initializers
  private static initializeDocument(document: Document): Document {
    return {
      ...document,
      id: document.id || `doc-${Date.now()}`,
      createdAt: document.createdAt || Date.now(),
      updatedAt: Date.now(),
      isActive: document.isActive ?? true,
      version: document.version || 1,
    };
  }

  private static initializeAgreement(agreement: Agreement): Agreement {
    return {
      ...agreement,
      id: agreement.id || `agr-${Date.now()}`,
      createdAt: agreement.createdAt || Date.now(),
      updatedAt: Date.now(),
      isActive: agreement.isActive ?? true,
      version: agreement.version || 1,
    };
  }

  private static initializeBankAccount(account: BankAccount): BankAccount {
    return {
      ...account,
      id: account.id || `bank-${Date.now()}`,
      createdAt: account.createdAt || Date.now(),
      updatedAt: Date.now(),
      isActive: account.isActive ?? true,
      version: account.version || 1,
    };
  }

  private static initializeGST(gst: GSTRegistration): GSTRegistration {
    return {
      ...gst,
      id: gst.id || `gst-${Date.now()}`,
      createdAt: gst.createdAt || Date.now(),
      updatedAt: Date.now(),
      isActive: gst.isActive ?? true,
      version: gst.version || 1,
    };
  }

  // --- Documents ---

  public static async getDocuments(providerId: string): Promise<Document[]> {
    const provider = await this.providerRepository.findById(providerId);
    if (!provider) {
      return [];
    }
    return provider.documents;
  }

  public static async uploadDocument(
    providerId: string,
    document: Document
  ): Promise<Document> {
    const provider = await this.providerRepository.findById(providerId);
    if (!provider) {
      throw new Error('Provider not found.');
    }

    const initialized = this.initializeDocument(document);
    const validated = DocumentSchema.validate(initialized);

    provider.documents.push(validated);
    provider.updatedAt = Date.now();

    await this.providerRepository.update(provider.id, {
      documents: provider.documents,
      updatedAt: provider.updatedAt,
    });

    ComplianceTelemetry.trackDocumentUploaded(providerId, validated.id);

    return validated;
  }

  public static async updateDocument(
    providerId: string,
    documentId: string,
    changes: Partial<Document>
  ): Promise<Document> {
    const provider = await this.providerRepository.findById(providerId);
    if (!provider) {
      throw new Error('Provider not found.');
    }

    const docIndex = provider.documents.findIndex((d) => d.id === documentId);
    if (docIndex === -1) {
      throw new Error('Document not found.');
    }

    const updated: Document = {
      ...provider.documents[docIndex],
      ...changes,
      updatedAt: Date.now(),
    };

    const validated = DocumentSchema.validate(updated);
    provider.documents[docIndex] = validated;
    provider.updatedAt = Date.now();

    await this.providerRepository.update(provider.id, {
      documents: provider.documents,
      updatedAt: provider.updatedAt,
    });

    return validated;
  }

  public static async renewDocument(
    providerId: string,
    documentId: string,
    newExpiryDate: number
  ): Promise<Document> {
    return this.updateDocument(providerId, documentId, {
      expiresAt: newExpiryDate,
    });
  }

  public static async expireDocument(
    providerId: string,
    documentId: string
  ): Promise<void> {
    const provider = await this.providerRepository.findById(providerId);
    if (!provider) {
      throw new Error('Provider not found.');
    }

    const docIndex = provider.documents.findIndex((d) => d.id === documentId);
    if (docIndex === -1) {
      throw new Error('Document not found.');
    }

    provider.documents[docIndex].expiresAt = Date.now();
    provider.documents[docIndex].updatedAt = Date.now();
    provider.updatedAt = Date.now();

    const validated = DocumentSchema.validate(provider.documents[docIndex]);
    provider.documents[docIndex] = validated;

    await this.providerRepository.update(provider.id, {
      documents: provider.documents,
      updatedAt: provider.updatedAt,
    });

    ComplianceTelemetry.trackDocumentExpired(providerId, documentId);
  }

  public static async deleteDocument(
    providerId: string,
    documentId: string
  ): Promise<void> {
    const provider = await this.providerRepository.findById(providerId);
    if (!provider) {
      throw new Error('Provider not found.');
    }

    provider.documents = provider.documents.filter((d) => d.id !== documentId);
    provider.updatedAt = Date.now();

    await this.providerRepository.update(provider.id, {
      documents: provider.documents,
      updatedAt: provider.updatedAt,
    });
  }

  // --- Agreements ---

  public static async getAgreements(providerId: string): Promise<Agreement[]> {
    const provider = await this.providerRepository.findById(providerId);
    if (!provider) {
      return [];
    }
    return provider.agreements;
  }

  public static async createAgreement(
    providerId: string,
    agreement: Agreement
  ): Promise<Agreement> {
    const provider = await this.providerRepository.findById(providerId);
    if (!provider) {
      throw new Error('Provider not found.');
    }

    const initialized = this.initializeAgreement(agreement);
    const validated = AgreementSchema.validate(initialized);

    provider.agreements.push(validated);
    provider.updatedAt = Date.now();

    await this.providerRepository.update(provider.id, {
      agreements: provider.agreements,
      updatedAt: provider.updatedAt,
    });

    ComplianceTelemetry.trackAgreementCreated(providerId, validated.id);

    return validated;
  }

  public static async updateAgreement(
    providerId: string,
    agreementId: string,
    changes: Partial<Agreement>
  ): Promise<Agreement> {
    const provider = await this.providerRepository.findById(providerId);
    if (!provider) {
      throw new Error('Provider not found.');
    }

    const agrIndex = provider.agreements.findIndex((a) => a.id === agreementId);
    if (agrIndex === -1) {
      throw new Error('Agreement not found.');
    }

    const updated: Agreement = {
      ...provider.agreements[agrIndex],
      ...changes,
      updatedAt: Date.now(),
    };

    const validated = AgreementSchema.validate(updated);
    provider.agreements[agrIndex] = validated;
    provider.updatedAt = Date.now();

    await this.providerRepository.update(provider.id, {
      agreements: provider.agreements,
      updatedAt: provider.updatedAt,
    });

    return validated;
  }

  public static async renewAgreement(
    providerId: string,
    agreementId: string,
    newExpiryDate: number
  ): Promise<Agreement> {
    return this.updateAgreement(providerId, agreementId, {
      expiresAt: newExpiryDate,
    });
  }

  public static async expireAgreement(
    providerId: string,
    agreementId: string
  ): Promise<void> {
    const provider = await this.providerRepository.findById(providerId);
    if (!provider) {
      throw new Error('Provider not found.');
    }

    const agrIndex = provider.agreements.findIndex((a) => a.id === agreementId);
    if (agrIndex === -1) {
      throw new Error('Agreement not found.');
    }

    provider.agreements[agrIndex].expiresAt = Date.now();
    provider.agreements[agrIndex].updatedAt = Date.now();
    provider.updatedAt = Date.now();

    const validated = AgreementSchema.validate(provider.agreements[agrIndex]);
    provider.agreements[agrIndex] = validated;

    await this.providerRepository.update(provider.id, {
      agreements: provider.agreements,
      updatedAt: provider.updatedAt,
    });

    ComplianceTelemetry.trackAgreementExpired(providerId, agreementId);
  }

  // --- Compliance Data ---

  public static async updateGSTRegistration(
    providerId: string,
    gst: GSTRegistration
  ): Promise<GSTRegistration> {
    const provider = await this.providerRepository.findById(providerId);
    if (!provider) {
      throw new Error('Provider not found.');
    }

    // Lightweight validation
    if (!gst.gstNumber?.trim()) {
      throw new Error('GST number is required.');
    }
    if (!gst.stateCode?.trim()) {
      throw new Error('State code is required.');
    }

    const initialized = this.initializeGST(gst);
    provider.gstRegistration = initialized;
    provider.updatedAt = Date.now();

    await this.providerRepository.update(provider.id, {
      gstRegistration: provider.gstRegistration,
      updatedAt: provider.updatedAt,
    });

    ComplianceTelemetry.trackGSTUpdated(providerId);

    return initialized;
  }

  public static async updateBankAccount(
    providerId: string,
    account: BankAccount
  ): Promise<BankAccount> {
    const provider = await this.providerRepository.findById(providerId);
    if (!provider) {
      throw new Error('Provider not found.');
    }

    // Lightweight validation
    if (!account.bankName?.trim()) {
      throw new Error('Bank name is required.');
    }
    if (!account.accountNumber?.trim()) {
      throw new Error('Account number is required.');
    }
    if (!account.accountHolderName?.trim()) {
      throw new Error('Account holder name is required.');
    }

    const initialized = this.initializeBankAccount(account);
    provider.bankAccount = initialized;
    provider.updatedAt = Date.now();

    await this.providerRepository.update(provider.id, {
      bankAccount: provider.bankAccount,
      updatedAt: provider.updatedAt,
    });

    ComplianceTelemetry.trackBankAccountUpdated(providerId);

    return initialized;
  }

  public static async getComplianceStatus(
    providerId: string
  ): Promise<ComplianceSummary> {
    const provider = await this.providerRepository.findById(providerId);
    if (!provider) {
      throw new Error('Provider not found.');
    }

    return ComplianceCalculator.calculate(provider);
  }
}
export default ComplianceService;
