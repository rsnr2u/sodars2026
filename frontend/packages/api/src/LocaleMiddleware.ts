import { InternalAxiosRequestConfig } from 'axios';
import { ApiMiddleware } from './ApiMiddleware';

export class LocaleMiddleware implements ApiMiddleware {
  public async execute(config: InternalAxiosRequestConfig): Promise<InternalAxiosRequestConfig> {
    config.headers['X-Timezone'] = Intl.DateTimeFormat().resolvedOptions().timeZone;
    config.headers['Accept-Language'] = navigator.language || 'en-US';
    return config;
  }
}
