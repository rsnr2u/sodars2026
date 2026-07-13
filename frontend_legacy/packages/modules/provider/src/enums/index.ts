export enum ProviderStatus {
  Pending = 'Pending',
  Submitted = 'Submitted',
  UnderReview = 'Under Review',
  Verified = 'Verified',
  Suspended = 'Suspended',
  Rejected = 'Rejected'
}

export enum VerificationStepStatus {
  Pending = 'Pending',
  Passed = 'Passed',
  Failed = 'Failed'
}

export enum AgreementStatus {
  Draft = 'Draft',
  Sent = 'Sent',
  Signed = 'Signed',
  Expired = 'Expired'
}

export enum BranchStatus {
  Active = 'Active',
  Inactive = 'Inactive',
  Offline = 'Offline'
}

export enum StaffStatus {
  Active = 'Active',
  Inactive = 'Inactive'
}

export enum DocumentStatus {
  Pending = 'Pending',
  Uploaded = 'Uploaded',
  Expired = 'Expired',
  Rejected = 'Rejected'
}

export * from './VerificationType';
export { default as VerificationType } from './VerificationType';
export * from './StaffRole';
export { default as StaffRole } from './StaffRole';
