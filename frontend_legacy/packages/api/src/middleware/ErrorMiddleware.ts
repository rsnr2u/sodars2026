import { HttpMiddleware, HttpRequest, HttpResponse, NextMiddleware } from '../client/HttpMiddleware';
import { ErrorMapper } from '../ErrorMapper';

export class ErrorMiddleware implements HttpMiddleware {
  public async execute(request: HttpRequest, next: NextMiddleware): Promise<HttpResponse> {
    try {
      return await next(request);
    } catch (error: any) {
      // Map error fields using standard ErrorMapper adapter
      const mappedError = ErrorMapper.map(error);
      throw mappedError;
    }
  }
}
export default ErrorMiddleware;
