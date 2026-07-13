import type { Branch } from '../types';

export class BranchSchema {
  public static validate(branch: Branch): Branch {
    if (!branch.id?.trim()) {
      throw new Error('Branch ID is required.');
    }

    if (!branch.providerId?.trim()) {
      throw new Error('Provider ID is required.');
    }

    if (!branch.name?.trim()) {
      throw new Error('Branch name is required.');
    }

    if (!branch.address) {
      throw new Error('Branch address is required.');
    }

    if (!branch.address.street?.trim()) {
      throw new Error('Street address is required.');
    }

    if (!branch.address.city?.trim()) {
      throw new Error('City is required.');
    }

    if (!branch.address.state?.trim()) {
      throw new Error('State is required.');
    }

    if (!branch.address.country?.trim()) {
      throw new Error('Country is required.');
    }

    if (!branch.phone?.number?.trim()) {
      throw new Error('Phone number is required.');
    }

    if (!branch.email?.value?.trim()) {
      throw new Error('Email address is required.');
    }

    return branch;
  }

  public static validateMany(branches: Branch[]): Branch[] {
    return branches.map(branch => this.validate(branch));
  }
}
export default BranchSchema;
