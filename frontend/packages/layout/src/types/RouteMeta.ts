import { IconType } from '@sodars/icons';
import { RouteParams } from './RouteParams';

export interface RouteMeta {
  id: string;
  module: string; // Required ownership module
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
