export interface ProviderBranch {
  id: string;
  providerId: string;
  name: string;
  city: string;
  state: string;
  status: 'Active' | 'Inactive';
}

export interface ProviderStaff {
  id: string;
  providerId: string;
  name: string;
  email: string;
  phone: string;
  role: string;
  status: 'Active' | 'Inactive';
}

export interface ProviderAgreement {
  id: string;
  providerId: string;
  title: string;
  status: 'Active' | 'Expired';
}

export interface ProviderVerification {
  id: string;
  providerId: string;
  status: 'Passed' | 'Failed' | 'Pending';
  remarks?: string;
}

export interface ProviderDocument {
  id: string;
  providerId: string;
  name: string;
  type: string;
  expiryDate: string;
  status: 'Valid' | 'Expired';
}

export interface ProviderContact {
  name: string;
  role: string;
  email: string;
  phone: string;
}

export interface ProviderBank {
  bankName: string;
  accountHolderName: string;
  accountNumber: string;
  routingNumber?: string;
}

export interface ProviderGST {
  gstNumber: string;
  stateCode: string;
}

export interface ProviderTimeline {
  id: string;
  event: string;
  timestamp: number;
}

export interface Provider {
  id: string;
  name: string;
  email: string;
  phone: string;
  gstNumber: string;
  status: 'Verified' | 'Under Review' | 'Pending' | 'Suspended';
  complianceStatus: 'Compliant' | 'Pending' | 'Expired';
  city: string;
  state: string;
  createdDate: string;
  gst?: ProviderGST;
  bank?: ProviderBank;
  contact?: ProviderContact;
  branches: ProviderBranch[];
  staff: ProviderStaff[];
  agreements: ProviderAgreement[];
  verifications: ProviderVerification[];
  documents: ProviderDocument[];
  timeline: ProviderTimeline[];
}
