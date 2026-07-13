import { IActivityRepository } from './interfaces/IActivityRepository';
import { Activity } from '../types';
import { apiClient } from '@sodars/api';

export class MockActivityRepository implements IActivityRepository {
  private static database: Activity[] = [];

  public async fetchActivities(enquiryId: string): Promise<Activity[]> {
    try {
      const response = await apiClient.get(`/crm/enquiries/${enquiryId}/activities`);
      return response.data as Activity[];
    } catch {
      return MockActivityRepository.database.filter(a => a.enquiryId === enquiryId);
    }
  }

  public async saveActivity(activity: Activity): Promise<Activity> {
    try {
      const response = await apiClient.post(`/crm/enquiries/${activity.enquiryId}/activities`, activity);
      return response.data as Activity;
    } catch {
      MockActivityRepository.database.push(activity);
      return activity;
    }
  }
}
export default MockActivityRepository;
