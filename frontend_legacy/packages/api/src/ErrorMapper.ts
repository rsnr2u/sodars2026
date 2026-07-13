import { AxiosError } from 'axios';

export enum ErrorCode {
  Validation = 'Validation',
  Authentication = 'Authentication',
  Authorization = 'Authorization',
  BusinessRule = 'BusinessRule',
  Conflict = 'Conflict',
  NotFound = 'NotFound',
  RateLimited = 'RateLimited',
  Network = 'Network',
  Timeout = 'Timeout',
  Unexpected = 'Unexpected'
}

export interface AppError {
  code: ErrorCode;
  message: string;
  status: number;
  correlationId?: string;
  validation?: Record<string, string[]>;
}

export class ErrorMapper {
  public static map(error: AxiosError): AppError {
    const correlationId = error.config?.headers?.['X-Correlation-Id'] as string | undefined;

    if (!error.response) {
      if (error.code === 'ECONNABORTED') {
        return {
          code: ErrorCode.Timeout,
          message: 'The request timed out. Please try again.',
          status: 0,
          correlationId,
        };
      }
      return {
        code: ErrorCode.Network,
        message: 'A network error occurred. Please check your connection.',
        status: 0,
        correlationId,
      };
    }

    const status = error.response.status;
    const responseData = error.response.data as any;
    const message = responseData?.message ?? error.message;

    switch (status) {
      case 400:
        return {
          code: ErrorCode.BusinessRule,
          message,
          status,
          correlationId,
        };
      case 401:
        return {
          code: ErrorCode.Authentication,
          message: 'Session is expired or unauthenticated. Please log in again.',
          status,
          correlationId,
        };
      case 403:
        return {
          code: ErrorCode.Authorization,
          message: 'You are not authorized to perform this action.',
          status,
          correlationId,
        };
      case 404:
        return {
          code: ErrorCode.NotFound,
          message: 'The requested resource was not found.',
          status,
          correlationId,
        };
      case 422:
        return {
          code: ErrorCode.Validation,
          message: 'Validation failed.',
          status,
          correlationId,
          validation: responseData?.errors ?? {},
        };
      case 429:
        return {
          code: ErrorCode.RateLimited,
          message: 'Too many requests. Please slow down.',
          status,
          correlationId,
        };
      default:
        return {
          code: ErrorCode.Unexpected,
          message: 'An unexpected system error occurred.',
          status,
          correlationId,
        };
    }
  }
}
