import { providerRepositories } from '../repositories';
import { BranchSchema } from '../schemas';
import type { Branch } from '../types';

export class BranchService {
  private static readonly repository = providerRepositories.branch;

  public static async getBranches(providerId?: string): Promise<Branch[]> {
    const branches = providerId
      ? await this.repository.findByProviderId(providerId)
      : await this.repository.findAll();
    return BranchSchema.validateMany(branches);
  }

  public static async getBranch(id: string): Promise<Branch | null> {
    const branch = await this.repository.findById(id);
    return branch ? BranchSchema.validate(branch) : null;
  }

  public static async getMainBranch(providerId: string): Promise<Branch | null> {
    const branches = await this.repository.findByProviderId(providerId);
    const mainBranch = branches.find(b => b.isMainBranch) ?? null;
    return mainBranch ? BranchSchema.validate(mainBranch) : null;
  }

  public static async getActiveBranches(): Promise<Branch[]> {
    const branches = await this.repository.findActive();
    return BranchSchema.validateMany(branches);
  }

  public static async createBranch(branch: Branch): Promise<Branch> {
    const validated = BranchSchema.validate(branch);
    const created = await this.repository.create(validated);
    return created;
  }

  public static async updateBranch(
    id: string,
    changes: Partial<Branch>
  ): Promise<Branch> {
    const updated = await this.repository.update(id, changes);
    return BranchSchema.validate(updated);
  }

  public static async deleteBranch(id: string): Promise<void> {
    await this.repository.delete(id);
  }

  public static async exists(providerId: string, name: string): Promise<boolean> {
    return this.repository.exists(name, providerId);
  }
}
export default BranchService;
