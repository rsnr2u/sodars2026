import {
  EmailAddress,
  PersonName,
  PhoneNumber,
} from '@sodars/business-core';
import {
  StaffRole,
  StaffStatus,
} from '../enums';
import { Staff } from '../types';

export const mockStaff: Staff[] = [
  {
    id: 'staff-001',
    providerId: 'prov_1',
    branchId: 'branch-1',
    employeeCode: 'EMP0001',
    name: {
      firstName: 'Ramesh',
      lastName: 'Kumar',
    } satisfies PersonName,
    email: {
      value: 'ramesh@sodars.com',
    } satisfies EmailAddress,
    phone: {
      countryCode: '+91',
      number: '9876543210',
    } satisfies PhoneNumber,
    designation: StaffRole.BranchManager,
    status: StaffStatus.Active,
    joiningDate: Date.now(),
    reportingTo: undefined,
    isActive: true,
    version: 1,
    createdAt: Date.now(),
    updatedAt: Date.now(),
  },
  {
    id: 'staff-002',
    providerId: 'prov_1',
    branchId: 'branch-2',
    employeeCode: 'EMP0002',
    name: {
      firstName: 'Suresh',
      lastName: 'Kumar',
    } satisfies PersonName,
    email: {
      value: 'suresh@sodars.com',
    } satisfies EmailAddress,
    phone: {
      countryCode: '+91',
      number: '8888888888',
    } satisfies PhoneNumber,
    designation: StaffRole.SalesExecutive,
    status: StaffStatus.Inactive,
    joiningDate: Date.now(),
    reportingTo: undefined,
    isActive: true,
    version: 1,
    createdAt: Date.now(),
    updatedAt: Date.now(),
  }
];
export default mockStaff;
