import { Activity } from '../../types';

export interface IActivityRepository {
  fetchActivities(enquiryId: string): Promise<Activity[]>;
  saveActivity(activity: Activity): Promise<Activity>;
}
