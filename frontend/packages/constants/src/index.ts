export const ROLES = {
  ADMIN: 'admin',
  PROVIDER: 'provider',
  CUSTOMER: 'customer',
  STAFF: 'staff',
} as const;

export const PERMISSIONS = {
  VIEW_PROVIDERS: 'providers:view',
  CREATE_PROVIDERS: 'providers:create',
  VERIFY_PROVIDERS: 'providers:verify',
  SUSPEND_PROVIDERS: 'providers:suspend',
  VIEW_INVENTORY: 'inventory:view',
  EDIT_INVENTORY: 'inventory:edit',
} as const;

export const REGEX = {
  GSTIN: /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/,
  EMAIL: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
  PHONE: /^[6-9]\d{9}$/,
} as const;

export const INDIAN_STATES = [
  { code: 'MH', name: 'Maharashtra' },
  { code: 'DL', name: 'Delhi' },
  { code: 'KA', name: 'Karnataka' },
  { code: 'TN', name: 'Tamil Nadu' },
  { code: 'TS', name: 'Telangana' },
  { code: 'HR', name: 'Haryana' },
  { code: 'UP', name: 'Uttar Pradesh' },
  { code: 'WB', name: 'West Bengal' },
  { code: 'GJ', name: 'Gujarat' },
  { code: 'KL', name: 'Kerala' },
  { code: 'AP', name: 'Andhra Pradesh' },
  { code: 'PB', name: 'Punjab' },
  { code: 'RJ', name: 'Rajasthan' },
  { code: 'MP', name: 'Madhya Pradesh' },
  { code: 'OD', name: 'Odisha' },
] as const;

export const COUNTRIES = [
  { code: 'IN', name: 'India' },
] as const;

export const PROVIDER_STATUS = {
  VERIFIED: 'Verified',
  UNDER_REVIEW: 'Under Review',
  PENDING: 'Pending',
  SUSPENDED: 'Suspended',
} as const;

export const INVENTORY_STATUS = {
  AVAILABLE: 'Available',
  BOOKED: 'Booked',
  MAINTENANCE: 'Maintenance',
} as const;
