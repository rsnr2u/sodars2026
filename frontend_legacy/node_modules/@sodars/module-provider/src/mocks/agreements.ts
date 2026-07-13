import { Agreement } from '../types';

export const mockAgreements: Agreement[] = [
  { id: 'agr_1', providerId: 'prov_1', title: 'Standard Master Hoardings Lease', file: { id: 'att_1', filename: 'metropolis_lease_2026.pdf', fileUrl: '/files/metropolis_lease_2026.pdf' }, expiresAt: Date.now() + 86400000 * 365, version: 1, isActive: true, createdAt: Date.now(), updatedAt: Date.now() }
];
export default mockAgreements;
