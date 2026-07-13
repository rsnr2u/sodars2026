import { HttpMiddleware, HttpRequest, HttpResponse, NextMiddleware } from '../client/HttpMiddleware';
import { useAuthStore } from '@sodars/store';

export class AuthMiddleware implements HttpMiddleware {
  public async execute(request: HttpRequest, next: NextMiddleware): Promise<HttpResponse> {
    const { token } = useAuthStore.getState();

    if (token) {
      return next({
        ...request,
        headers: {
          ...request.headers,
          'Authorization': `Bearer ${token}`
        }
      });
    }

    return next(request);
  }
}
export default AuthMiddleware;
