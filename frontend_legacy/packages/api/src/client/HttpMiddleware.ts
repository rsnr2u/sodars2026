export type HttpMethod =
  | 'GET'
  | 'POST'
  | 'PUT'
  | 'PATCH'
  | 'DELETE'
  | 'OPTIONS'
  | 'HEAD';

export interface HttpRequest {
  url: string;
  method: HttpMethod;
  headers: Record<string, string>;
  body?: unknown;
  timeout?: number;
  params?: Record<string, string | number>;
}

export interface HttpResponse {
  status: number;
  headers: Record<string, string>;
  data: unknown;
}

export type NextMiddleware = (request: HttpRequest) => Promise<HttpResponse>;

export interface HttpMiddleware {
  execute(request: HttpRequest, next: NextMiddleware): Promise<HttpResponse>;
}
export default HttpMiddleware;
