import { Customer } from '../../types';

export interface ICustomerRepository {
  fetchCustomers(): Promise<Customer[]>;
  findCustomer(id: string): Promise<Customer | null>;
  saveCustomer(customer: Customer): Promise<Customer>;
}
