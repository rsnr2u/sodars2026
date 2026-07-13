import { HttpMiddleware, HttpRequest, HttpResponse, NextMiddleware } from '../client/HttpMiddleware';

export class RetryMiddleware implements HttpMiddleware {
  private maxRetries: number;
  private delayMs: number;

  constructor(maxRetries = 2, delayMs = 1000) {
    this.maxRetries = maxRetries;
    this.delayMs = delayMs;
  }

  public async execute(request: HttpRequest, next: NextMiddleware): Promise<HttpResponse> {
    let attempt = 0;

    const executeWithRetry = async (): Promise<HttpResponse> => {
      try {
        return await next(request);
      } catch (error) {
        attempt++;
        if (attempt <= this.maxRetries && this.isRetryable(error)) {
          const backoff = this.delayMs * Math.pow(2, attempt - 1);
          console.warn(`[RetryMiddleware] Request failed. Retrying in ${backoff}ms (Attempt ${attempt}/${this.maxRetries})...`);
          await new Promise(resolve => setTimeout(resolve, backoff));
          return executeWithRetry();
        }
        throw error;
      }
    };

    return executeWithRetry();
  }

  private isRetryable(error: any): boolean {
    // Retry on network errors or 5xx status codes
    if (!error.status) return true; // Network errors usually don't have HTTP status
    return error.status >= 500;
  }
}
export default RetryMiddleware;
