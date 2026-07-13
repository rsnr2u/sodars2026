import { Document } from '../types';

export const mockDocuments: Document[] = [
  { id: 'doc_1', providerId: 'prov_1', name: 'Trade License Copy', type: 'Trade License', file: { id: 'att_2', filename: 'trade_lic_2026.pdf', fileUrl: '/files/trade_lic_2026.pdf' }, expiresAt: Date.now() + 86400000 * 180, version: 1, isActive: true, createdAt: Date.now(), updatedAt: Date.now() }
];
export default mockDocuments;
