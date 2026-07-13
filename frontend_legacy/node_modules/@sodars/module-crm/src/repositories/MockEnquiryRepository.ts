import { IEnquiryRepository } from './interfaces/IEnquiryRepository';
import { Enquiry } from '../types';
import { mockEnquiries } from '../mocks/enquiries';
import { apiClient } from '@sodars/api';

export class MockEnquiryRepository implements IEnquiryRepository {
  private static database: Enquiry[] = [...mockEnquiries];

  public async fetchEnquiries(): Promise<Enquiry[]> {
    try {
      const response = await apiClient.get('/crm/enquiries');
      return response.data as Enquiry[];
    } catch {
      return MockEnquiryRepository.database;
    }
  }

  public async findEnquiry(id: string): Promise<Enquiry | null> {
    try {
      const response = await apiClient.get(`/crm/enquiries/${id}`);
      return response.data as Enquiry;
    } catch {
      return MockEnquiryRepository.database.find(e => e.id === id) || null;
    }
  }

  public async saveEnquiry(enquiry: Enquiry): Promise<Enquiry> {
    try {
      const response = await apiClient.post('/crm/enquiries', enquiry);
      return response.data as Enquiry;
    } catch {
      const index = MockEnquiryRepository.database.findIndex(e => e.id === enquiry.id);
      if (index >= 0) {
        MockEnquiryRepository.database[index] = enquiry;
      } else {
        MockEnquiryRepository.database.push(enquiry);
      }
      return enquiry;
    }
  }
}
export default MockEnquiryRepository;
