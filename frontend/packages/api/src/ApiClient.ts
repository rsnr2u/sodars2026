import axios from 'axios';
import { HttpMiddleware, HttpRequest, HttpResponse, HttpMethod } from './client/HttpMiddleware';
import { CorrelationIdMiddleware } from './middleware/CorrelationIdMiddleware';
import { RequestContextMiddleware } from './middleware/RequestContextMiddleware';
import { AuthMiddleware } from './middleware/AuthMiddleware';
import { FeatureFlagMiddleware } from './middleware/FeatureFlagMiddleware';
import { RetryMiddleware } from './middleware/RetryMiddleware';
import { TimeoutMiddleware } from './middleware/TimeoutMiddleware';
import { TelemetryMiddleware } from './middleware/TelemetryMiddleware';
import { ErrorMiddleware } from './middleware/ErrorMiddleware';

export class ApiClient {
  private static middlewares: HttpMiddleware[] = [
    new CorrelationIdMiddleware(),
    new RequestContextMiddleware(),
    new AuthMiddleware(),
    new FeatureFlagMiddleware(),
    new RetryMiddleware(),
    new TimeoutMiddleware(),
    new TelemetryMiddleware(),
    new ErrorMiddleware(),
  ];

  public static async execute(request: HttpRequest): Promise<HttpResponse> {
    const axiosInstance = axios.create({
      baseURL: '/api',
      headers: {
        'Content-Type': 'application/json',
      },
    });

    let index = 0;
    const next = async (req: HttpRequest): Promise<HttpResponse> => {
      if (index < this.middlewares.length) {
        const middleware = this.middlewares[index++];
        return middleware.execute(req, next);
      }

      // Final Axios execution
      const response = await axiosInstance({
        url: req.url,
        method: req.method,
        headers: req.headers,
        data: req.body,
        timeout: req.timeout,
      });

      return {
        status: response.status,
        headers: Object.fromEntries(
          Object.entries(response.headers || {}).map(([k, v]) => [k, String(v)])
        ),
        data: response.data,
      };
    };

    return next(request);
  }
}

export const apiClient = {
  get: (url: string, headers?: Record<string, string>) => 
    ApiClient.execute({ url, method: 'GET', headers: headers || {} }),
  
  post: (url: string, body?: unknown, headers?: Record<string, string>) => 
    ApiClient.execute({ url, method: 'POST', headers: headers || {}, body }),
  
  put: (url: string, body?: unknown, headers?: Record<string, string>) => 
    ApiClient.execute({ url, method: 'PUT', headers: headers || {}, body }),
  
  patch: (url: string, body?: unknown, headers?: Record<string, string>) => 
    ApiClient.execute({ url, method: 'PATCH', headers: headers || {}, body }),
  
  delete: (url: string, headers?: Record<string, string>) => 
    ApiClient.execute({ url, method: 'DELETE', headers: headers || {} }),
};

export default apiClient;
