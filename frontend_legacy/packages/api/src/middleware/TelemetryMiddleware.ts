import { HttpMiddleware, HttpRequest, HttpResponse, NextMiddleware } from '../client/HttpMiddleware';
import { Telemetry, Severity } from '@sodars/observability';

export class TelemetryMiddleware implements HttpMiddleware {
  public async execute(request: HttpRequest, next: NextMiddleware): Promise<HttpResponse> {
    const startTime = Date.now();
    const correlationId = request.headers['X-Correlation-Id'];

    Telemetry.track(
      'api:request',
      Severity.Info,
      { url: request.url, method: request.method },
      'api',
      undefined,
      correlationId
    );

    try {
      const response = await next(request);
      const duration = Date.now() - startTime;

      Telemetry.track(
        'api:success',
        Severity.Info,
        { url: request.url, method: request.method, status: response.status },
        'api',
        duration,
        correlationId
      );

      return response;
    } catch (error: any) {
      const duration = Date.now() - startTime;
      
      Telemetry.track(
        'api:failure',
        Severity.Error,
        { 
          url: request.url, 
          method: request.method, 
          status: error.status || 500, 
          message: error.message || 'Unknown network error' 
        },
        'api',
        duration,
        correlationId
      );

      throw error;
    }
  }
}
export default TelemetryMiddleware;
