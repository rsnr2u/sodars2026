import { providerRepositories } from '../repositories';
import { ProviderSchema } from '../schemas';
import { ProviderTelemetry } from '../telemetry';
import type { Provider } from '../types';

export class ProviderService {
  private static readonly repository = providerRepositories.provider;

  public static async getProviders(): Promise<Provider[]> {
    const providers = await this.repository.findAll();
    return ProviderSchema.validateMany(providers);
  }

  public static async getProvider(id: string): Promise<Provider | null> {
    const provider = await this.repository.findById(id);
    return provider ? ProviderSchema.validate(provider) : null;
  }

  public static async searchProviders(query: string): Promise<Provider[]> {
    const providers = await this.repository.search(query);
    return ProviderSchema.validateMany(providers);
  }

  public static async createProvider(
    provider: Provider,
  ): Promise<Provider> {
    const validated = ProviderSchema.validate(provider);
    const created = await this.repository.create(validated);
    ProviderTelemetry.trackCreated(created.id);
    return created;
  }

  public static async updateProvider(
    id: string,
    changes: Partial<Provider>,
  ): Promise<Provider> {
    const updated = await this.repository.update(id, changes);
    ProviderTelemetry.trackUpdated(id);
    return ProviderSchema.validate(updated);
  }

  public static async deleteProvider(id: string): Promise<void> {
    await this.repository.delete(id);
    ProviderTelemetry.trackDeleted(id);
  }

  public static async verifyProvider(id: string): Promise<void> {
    await this.repository.verify(id);
    ProviderTelemetry.trackVerified(id);
  }

  public static async rejectProvider(
    id: string,
    reason: string,
  ): Promise<void> {
    await this.repository.reject(id, reason);
    ProviderTelemetry.trackRejected(id, reason);
  }

  public static async suspendProvider(id: string): Promise<void> {
    await this.repository.suspend(id);
    ProviderTelemetry.trackSuspended(id);
  }

  public static async activateProvider(id: string): Promise<void> {
    await this.repository.activate(id);
    ProviderTelemetry.trackActivated(id);
  }
}
export default ProviderService;
