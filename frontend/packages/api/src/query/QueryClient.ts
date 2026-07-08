import { QueryDefaults } from './QueryDefaults';

// Mock TanStack query client interface wrapper to decouple from library dependencies
export class QueryClient {
  private config: any;

  constructor(config?: any) {
    this.config = config || QueryDefaults;
  }

  public getDefaults() {
    return this.config;
  }
}
export default QueryClient;
