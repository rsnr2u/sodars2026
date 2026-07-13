import type { Staff } from '../types';

export class StaffSchema {
  public static validate(staff: Staff): Staff {
    if (!staff.id?.trim()) {
      throw new Error('Staff ID is required.');
    }

    if (!staff.providerId?.trim()) {
      throw new Error('Provider ID is required.');
    }

    if (!staff.name?.firstName?.trim()) {
      throw new Error('First name is required.');
    }

    if (!staff.name?.lastName?.trim()) {
      throw new Error('Last name is required.');
    }

    if (!staff.email?.value?.trim()) {
      throw new Error('Email address is required.');
    }

    if (!staff.phone?.number?.trim()) {
      throw new Error('Phone number is required.');
    }

    if (!staff.designation) {
      throw new Error('Staff designation is required.');
    }

    if (!staff.status) {
      throw new Error('Staff status is required.');
    }

    return staff;
  }

  public static validateMany(staffMembers: Staff[]): Staff[] {
    return staffMembers.map(staff => this.validate(staff));
  }
}
export default StaffSchema;
