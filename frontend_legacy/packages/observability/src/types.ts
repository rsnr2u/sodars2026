export type AuthEvents = 'auth:login_success' | 'auth:login_failure' | 'auth:logout';
export type NavigationEvents = 'navigation:changed' | 'navigation:shortcut_toggle';
export type ApiEvents = 'api:request' | 'api:success' | 'api:failure';
export type WidgetEvents = 'widget:loaded' | 'widget:removed';
export type CommandEvents = 'command:registered' | 'command:executed';
export type ModuleEvents =
  | 'module:bootstrapped'
  | 'module:registered'
  | 'module:started'
  | 'module:stopped'
  | 'module:shutdown';

export enum PerformanceEvents {
  FirstPaint = 'perf:first_paint',
  FirstContentfulPaint = 'perf:first_contentful_paint',
  RouteLoaded = 'perf:route_loaded',
  WidgetRendered = 'perf:widget_rendered',
  QueryCompleted = 'perf:query_completed'
}

export type TelemetryEvent =
  | AuthEvents
  | NavigationEvents
  | ApiEvents
  | WidgetEvents
  | CommandEvents
  | ModuleEvents
  | PerformanceEvents;

export enum Severity {
  Debug,
  Info,
  Warning,
  Error,
  Critical
}

export interface TelemetryPayload {
  readonly timestamp: number;
  readonly event: TelemetryEvent;
  readonly module?: string;
  readonly organizationId?: string;
  readonly correlationId?: string;
  readonly duration?: number;
  readonly severity: Severity;
  readonly properties?: Record<string, unknown>;
}

export interface ObservabilityAdapter {
  log(message: string, severity: Severity, context?: Record<string, unknown>): void;
  trackEvent(payload: TelemetryPayload): void;
  trackError(error: Error, severity?: Severity): void;
}
