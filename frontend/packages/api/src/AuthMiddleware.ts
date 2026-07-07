import { InternalAxiosRequestConfig } from 'axios';
import { ApiMiddleware } from './ApiMiddleware';
import { useAuthStore } from '@sodars/store';

export class AuthMiddleware implements ApiMiddleware {
  public async execute(config: InternalAxiosRequestConfig): Promise<InternalAxiosRequestConfig> {
    const token = useAuthStore.getState().token;
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  }
}
