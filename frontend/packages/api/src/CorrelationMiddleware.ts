import { InternalAxiosRequestConfig } from 'axios';
import { ApiMiddleware } from './ApiMiddleware';

export class CorrelationMiddleware implements ApiMiddleware {
  public async execute(config: InternalAxiosRequestConfig): Promise<InternalAxiosRequestConfig> {
    const correlationId = this.generateUuid();
    const requestId = this.generateUuid();

    config.headers['X-Correlation-Id'] = correlationId;
    config.headers['X-Request-Id'] = requestId;

    return config;
  }

  private generateUuid(): string {
    return Math.random().toString(36).substring(2, 15) + 
           Math.random().toString(36).substring(2, 15);
  }
}
