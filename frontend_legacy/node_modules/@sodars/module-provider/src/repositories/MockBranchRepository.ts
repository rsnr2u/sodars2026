import { MockRepository } from '@sodars/api';
import { BranchStatus } from '../enums';
import { mockBranches } from '../mocks';
import { Branch } from '../types';
import { IBranchRepository } from './interfaces';

export class MockBranchRepository
  extends MockRepository<Branch>
  implements IBranchRepository {

  constructor() {
    super(mockBranches);
  }

  public async findByProviderId(providerId: string): Promise<Branch[]> {
    return this.filter(branch => branch.providerId === providerId);
  }

  public async findActive(): Promise<Branch[]> {
    return this.filter(
      branch => branch.status === BranchStatus.Active,
    );
  }

  public async exists(
    name: string,
    providerId: string,
  ): Promise<boolean> {
    return this.items.some(
      branch =>
        branch.providerId === providerId &&
        branch.name.toLowerCase() === name.toLowerCase(),
    );
  }
}
export default MockBranchRepository;
