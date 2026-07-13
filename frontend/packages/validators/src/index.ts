import { z } from 'zod';

export const providerValidatorSchema = z.object({
  id: z.string(),
  name: z.string().min(1, 'Legal Name is required'),
  email: z.string().email('Invalid email address'),
  phone: z.string().min(10, 'Phone number must be at least 10 digits'),
  gstNumber: z.string().regex(/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/, 'Invalid GST number format'),
  status: z.enum(['Verified', 'Under Review', 'Pending', 'Suspended']),
  complianceStatus: z.enum(['Compliant', 'Pending', 'Expired']),
  city: z.string().min(1, 'City is required'),
  state: z.string().min(1, 'State is required'),
  createdDate: z.string(),
  agreementsCount: z.number().nonnegative(),
});
