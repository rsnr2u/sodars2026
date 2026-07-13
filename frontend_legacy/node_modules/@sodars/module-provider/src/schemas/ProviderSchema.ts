import { Provider } from '../types';

export class ProviderSchema {
  public static validate(provider: Provider): Provider {
    if (!provider) {
      throw new Error('Provider payload is required.');
    }
    if (!provider.name || !provider.name.trim()) {
      throw new Error('Provider name is required.');
    }
    if (!provider.email || !provider.email.trim()) {
      throw new Error('Email is required.');
    }
    return provider;
  }

  public static validateMany(list: Provider[]): Provider[] {
    if (!Array.isArray(list)) {
      throw new Error('Providers payload must be an array.');
    }
    return list.map(item => this.validate(item));
  }
}
export default ProviderSchema;
