import { ICustomerRepository } from './interfaces/ICustomerRepository';
import { Customer } from '../types';
import { mockCustomers } from '../mocks/customers';
import { apiClient } from '@sodars/api';

export class MockCustomerRepository implements ICustomerRepository {
  private static database: Customer[] = [...mockCustomers];

  public async fetchCustomers(): Promise<Customer[]> {
    try {
      const response = await apiClient.get('/crm/customers');
      return response.data as Customer[];
    } catch {
      return MockCustomerRepository.database;
    }
  }

  public async findCustomer(id: string): Promise<Customer | null> {
    try {
      const response = await apiClient.get(`/crm/customers/${id}`);
      return response.data as Customer;
    } catch {
      return MockCustomerRepository.database.find(c => c.id === id) || null;
    }
  }

  public async saveCustomer(customer: Customer): Promise<Customer> {
    try {
      const response = await apiClient.post('/crm/customers', customer);
      return response.data as Customer;
    } catch {
      const index = MockCustomerRepository.database.findIndex(c => c.id === customer.id);
      if (index >= 0) {
        MockCustomerRepository.database[index] = customer;
      } else {
        MockCustomerRepository.database.push(customer);
      }
      return customer;
    }
  }
}
export default MockCustomerRepository;
