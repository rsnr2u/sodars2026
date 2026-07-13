import { MockProviderRepository } from './MockProviderRepository';
import { MockBranchRepository } from './MockBranchRepository';
import { MockStaffRepository } from './MockStaffRepository';
import { IProviderRepository, IBranchRepository, IStaffRepository } from './interfaces';

export class ProviderRepositoryRegistry {
  public readonly provider: IProviderRepository;
  public readonly branch: IBranchRepository;
  public readonly staff: IStaffRepository;

  constructor() {
    this.provider = new MockProviderRepository();
    this.branch = new MockBranchRepository();
    this.staff = new MockStaffRepository();
  }
}

export const providerRepositories = new ProviderRepositoryRegistry();
export default providerRepositories;
