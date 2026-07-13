import { BaseRepository } from '@sodars/api';
import { Branch } from '../../types';

export interface IBranchRepository extends BaseRepository<Branch> {
  findByProviderId(providerId: string): Promise<Branch[]>;
  findActive(): Promise<Branch[]>;
  exists(name: string, providerId: string): Promise<boolean>;
}
export default IBranchRepository;
