import { MockCustomerRepository } from '../repositories/MockCustomerRepository';
import { CustomerSchema } from '../schemas/CustomerSchema';
import { Customer } from '../types';
import { CrmTelemetry } from '../telemetry/CrmTelemetry';

export class CustomerService {
  private static repository = new MockCustomerRepository();

  public static async getCustomers(): Promise<Customer[]> {
    const raw = await this.repository.fetchCustomers();
    return CustomerSchema.validateMany(raw);
  }

  public static async getCustomer(id: string): Promise<Customer | null> {
    const raw = await this.repository.findCustomer(id);
    if (!raw) return null;
    return CustomerSchema.validate(raw);
  }

  public static async createCustomer(payload: Partial<Customer>): Promise<Customer> {
    const newCustomer: Customer = {
      id: payload.id || `cust_${Date.now()}`,
      companyName: payload.companyName || '',
      email: payload.email || '',
      phone: payload.phone || '',
      contacts: payload.contacts || [],
      addresses: payload.addresses || [],
      timeline: [
        { id: `t_${Date.now()}`, type: 'Customer Created', timestamp: Date.now(), details: 'Company profile registered.' }
      ],
      createdAt: Date.now()
    };

    const validated = CustomerSchema.validate(newCustomer);
    const saved = await this.repository.saveCustomer(validated);

    CrmTelemetry.customerCreated(saved);
    return saved;
  }
}
export default CustomerService;
