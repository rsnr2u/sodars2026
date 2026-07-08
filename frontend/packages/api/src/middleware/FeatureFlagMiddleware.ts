import { HttpMiddleware, HttpRequest, HttpResponse, NextMiddleware } from '../client/HttpMiddleware';
import { FeatureFlagStore } from '@sodars/config';

export class FeatureFlagMiddleware implements HttpMiddleware {
  public async execute(request: HttpRequest, next: NextMiddleware): Promise<HttpResponse> {
    // Inject active flags config summary header
    const headers = {
      ...request.headers,
      'X-Feature-Flags-Active': 'true'
    };

    return next({
      ...request,
      headers
    });
  }
}
export default FeatureFlagMiddleware;
