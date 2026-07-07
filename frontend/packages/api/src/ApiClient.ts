import axios, { AxiosInstance, InternalAxiosRequestConfig, AxiosResponse, AxiosError } from 'axios';
import { ApiMiddleware } from './ApiMiddleware';
import { AuthMiddleware } from './AuthMiddleware';
import { TenantMiddleware } from './TenantMiddleware';
import { LocaleMiddleware } from './LocaleMiddleware';
import { CorrelationMiddleware } from './CorrelationMiddleware';
import { ErrorMapper } from './ErrorMapper';

export class ApiClient {
  private static instance: AxiosInstance | null = null;
  private static middlewares: ApiMiddleware[] = [
    new CorrelationMiddleware(),
    new AuthMiddleware(),
    new TenantMiddleware(),
    new LocaleMiddleware(),
  ];

  public static get(): AxiosInstance {
    if (!this.instance) {
      this.instance = axios.create({
        baseURL: '/api',
        timeout: 30000,
        headers: {
          'Content-Type': 'application/json',
        },
      });

      // 1. Request Interceptors Pipeline
      this.instance.interceptors.request.use(
        async (config) => {
          let currentConfig = config;
          for (const middleware of this.middlewares) {
            currentConfig = await middleware.execute(currentConfig);
          }
          return currentConfig;
        },
        (error) => Promise.reject(error)
      );

      // 2. Response Interceptors Pipeline
      this.instance.interceptors.response.use(
        (response: AxiosResponse) => response,
        (error: AxiosError) => {
          const appError = ErrorMapper.map(error);
          return Promise.reject(appError);
        }
      );
    }

    return this.instance;
  }
}

export const apiClient = ApiClient.get();
export default apiClient;
