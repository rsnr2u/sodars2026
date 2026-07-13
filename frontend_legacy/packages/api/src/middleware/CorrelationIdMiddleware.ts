import { HttpMiddleware, HttpRequest, HttpResponse, NextMiddleware } from '../client/HttpMiddleware';

export class CorrelationIdMiddleware implements HttpMiddleware {
  public async execute(request: HttpRequest, next: NextMiddleware): Promise<HttpResponse> {
    const correlationId = this.generateUuid();
    const requestId = this.generateUuid();

    // Attach correlation and request headers
    const headers = {
      ...request.headers,
      'X-Correlation-Id': correlationId,
      'X-Request-Id': requestId
    };

    return next({
      ...request,
      headers
    });
  }

  private generateUuid(): string {
    return Math.random().toString(36).substring(2, 15) + 
           Math.random().toString(36).substring(2, 15);
  }
}
export default CorrelationIdMiddleware;
