import { AuditLog } from '../types/audit-log';

export const mockAuditLogs: AuditLog[] = [
  { id: 'aud_1', timestamp: Date.now() - 600000, actorId: 'usr_1', action: 'user.create', details: 'Created account usr_2 (Jane Smith)' },
  { id: 'aud_2', timestamp: Date.now() - 3600000, actorId: 'usr_1', action: 'role.update', details: 'Modified permissions for manager role' }
];
export default mockAuditLogs;
