export enum ScheduleStatus {
  Draft = 'draft',
  Planned = 'planned',
  Validated = 'validated',
  Optimized = 'optimized',
  Assigned = 'assigned',
  Approved = 'approved',
  Dispatched = 'dispatched',
  InProgress = 'in_progress',
  Completed = 'completed',
  Archived = 'archived',
  Cancelled = 'cancelled',
  Delayed = 'delayed',
  Suspended = 'suspended',
  Failed = 'failed',
}

export enum ResourceState {
  Available = 'available',
  Allocated = 'allocated',
  Transit = 'transit',
  Maintenance = 'maintenance',
  Inactive = 'inactive',
}

export interface UserDTO {
  id: string;
  name: string;
  email: string;
  roles: string[];
  permissions: string[];
  organizations?: OrganizationDTO[];
}

export interface OrganizationDTO {
  id: string;
  name: string;
  slug: string;
}

export interface ScheduleDTO {
  id: string;
  schedule_number: string;
  organization_id: string;
  name: string;
  schedule_type: string;
  status: ScheduleStatus;
  start_time: string;
  end_time: string;
}

export interface ResourceDTO {
  id: string;
  organization_id: string;
  resource_type: string;
  display_name: string;
  status: string;
  skills: string[];
}
