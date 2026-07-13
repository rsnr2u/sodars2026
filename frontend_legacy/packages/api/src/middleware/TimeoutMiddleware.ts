import { HttpMiddleware, HttpRequest, HttpResponse, NextMiddleware } from '../client/HttpMiddleware';

export class TimeoutMiddleware implements HttpMiddleware {
  private defaultTimeout: number;

  constructor(defaultTimeout = 10000) { // 10 seconds default
    this.defaultTimeout = defaultTimeout;
  }

  public async execute(request: HttpRequest, next: NextMiddleware): Promise<HttpResponse> {
    const timeout = request.timeout || this.defaultTimeout;

    return new Promise<HttpResponse>((resolve, reject) => {
      const timer = setTimeout(() => {
        reject(new Error(`[TimeoutMiddleware] Request timed out after ${timeout}ms.`));
      }, timeout);

      next(request)
        .then(res => {
          clearTimeout(timer);
          resolve(res);
        })
        .catch(err => {
          clearTimeout(timer);
          reject(err);
        });
    });
  }
}
export default TimeoutMiddleware;
