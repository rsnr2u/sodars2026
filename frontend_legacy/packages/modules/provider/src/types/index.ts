import { EntityBase, Address, ContactInfo, AttachmentReference, TimelineEvent, PhoneNumber, EmailAddress, PersonName } from '@sodars/business-core';
import { ProviderStatus, VerificationStepStatus, BranchStatus, StaffStatus, StaffRole, VerificationType } from '../enums';

export interface VerificationStep {
  id: string;
  name: string;
  status: VerificationStepStatus;
  notes?: string;
  updatedAt: number;
}

export interface Verification extends EntityBase {
  providerId: string;
  type: VerificationType;
  status: ProviderStatus;
  steps: VerificationStep[];
  comments?: string;
}

export interface Document extends EntityBase {
  providerId: string;
  name: string;
  type: string; // e.g. "GST", "PAN", "Trade License"
  file: AttachmentReference;
  expiresAt?: number;
}

export interface Agreement extends EntityBase {
  providerId: string;
  title: string;
  file: AttachmentReference;
  signedAt?: number;
  expiresAt: number;
}

export interface BankAccount extends EntityBase {
  providerId: string;
  bankName: string;
  accountNumber: string;
  accountHolderName: string;
  routingNumber?: string;
  ifscCode?: string;
}

export interface GSTRegistration extends EntityBase {
  providerId: string;
  gstNumber: string;
  stateCode: string;
  registeredAddress: Address;
}

export interface ProviderNote extends EntityBase {
  providerId: string;
  authorId: string;
  content: string;
}

export interface Provider extends EntityBase {
  name: string;
  email: string;
  phone: string;
  status: ProviderStatus;
  rejectionReason?: string;
  primaryContact: ContactInfo;
  gstRegistration?: GSTRegistration;
  bankAccount?: BankAccount;
  agreements: Agreement[];
  documents: Document[];
  verifications: Verification[];
  notes: ProviderNote[];
  timeline: TimelineEvent[];
}

export interface Branch extends EntityBase {
  providerId: string;
  name: string;
  address: Address;
  phone: PhoneNumber;
  email: EmailAddress;
  isMainBranch: boolean;
  status: BranchStatus;
}

export interface Staff extends EntityBase {
  providerId: string;
  branchId?: string;
  employeeCode: string;
  name: PersonName;
  email: EmailAddress;
  phone: PhoneNumber;
  designation: StaffRole;
  status: StaffStatus;
  reportingTo?: string;
  joiningDate: number;
}

export interface ComplianceSummary {
  providerId: string;
  documentsValid: number;
  documentsExpired: number;
  agreementsActive: number;
  agreementsExpired: number;
  gstVerified: boolean;
  bankVerified: boolean;
  overallStatus: 'Compliant' | 'Pending' | 'Expired';
}

