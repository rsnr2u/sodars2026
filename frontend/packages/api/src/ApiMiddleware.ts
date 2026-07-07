import { InternalAxiosRequestConfig } from 'axios';

export interface ApiMiddleware {
  execute(config: InternalAxiosRequestConfig): Promise<InternalAxiosRequestConfig>;
}
