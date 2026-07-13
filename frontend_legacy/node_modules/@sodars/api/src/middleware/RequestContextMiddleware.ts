import { HttpMiddleware, HttpRequest, HttpResponse, NextMiddleware } from '../client/HttpMiddleware';
import { RequestContext } from '../RequestContext';

export class RequestContextMiddleware implements HttpMiddleware {
  private context: RequestContext;

  constructor(context?: RequestContext) {
    this.context = context || RequestContext.builder().build();
  }

  public async execute(request: HttpRequest, next: NextMiddleware): Promise<HttpResponse> {
    const contextHeaders: Record<string, string> = {
      'X-Timezone': this.context.timezone,
      'Accept-Language': this.context.locale,
      'X-Device-Id': this.context.deviceId || '',
      'X-Client-Version': this.context.clientVersion,
      'X-Platform-Version': this.context.platformVersion,
    };

    if (this.context.organizationId) {
      contextHeaders['X-Organization-Id'] = this.context.organizationId;
    }
    if (this.context.branchId) {
      contextHeaders['X-Branch-Id'] = this.context.branchId;
    }
    if (this.context.workspaceId) {
      contextHeaders['X-Workspace-Id'] = this.context.workspaceId;
    }

    return next({
      ...request,
      headers: {
        ...request.headers,
        ...contextHeaders
      }
    });
  }
}
export default RequestContextMiddleware;
