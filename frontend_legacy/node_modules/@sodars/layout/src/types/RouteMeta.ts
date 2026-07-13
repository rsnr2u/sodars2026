import { IconType } from '@sodars/icons';
import { RouteParams } from './RouteParams';
import { ModuleId } from '@sodars/sdk';

export interface RouteMeta {
  id: string;
  module: ModuleId; // Required ownership module
  resource?: string; // Optional resource (e.g. 'lead')
  action?: string; // Optional action (e.g. 'view')
  layout: "admin" | "auth"; // Required layout context
  title: string;
  breadcrumb?: string | ((params: RouteParams) => string);
  icon?: IconType;
  permission?: string;
  featureFlag?: string;
  preload?: boolean;
  cache?: boolean;
  telemetry?: boolean;
  searchable?: boolean;
  disabled?: boolean;
}
