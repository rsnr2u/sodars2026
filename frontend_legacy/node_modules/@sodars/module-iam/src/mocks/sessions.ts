import { Session } from '../types/session';

export const mockSessions: Session[] = [
  { id: 'sess_1', userId: 'usr_1', device: 'Chrome / Windows', ipAddress: '192.168.1.1', lastActive: Date.now() - 5000 },
  { id: 'sess_2', userId: 'usr_2', device: 'Safari / macOS', ipAddress: '192.168.1.20', lastActive: Date.now() - 3600000 }
];
export default mockSessions;
