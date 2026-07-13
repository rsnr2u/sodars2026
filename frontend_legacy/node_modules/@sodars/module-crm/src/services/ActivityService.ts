import { MockActivityRepository } from '../repositories/MockActivityRepository';
import { Activity, ActivityType } from '../types';

export class ActivityService {
  private static repository = new MockActivityRepository();

  public static async getActivities(enquiryId: string): Promise<Activity[]> {
    return this.repository.fetchActivities(enquiryId);
  }

  public static async logActivity(enquiryId: string, type: ActivityType, details: string): Promise<Activity> {
    const activity: Activity = {
      id: `act_${Date.now()}`,
      enquiryId,
      type,
      details,
      timestamp: Date.now()
    };
    return this.repository.saveActivity(activity);
  }
}
export default ActivityService;
