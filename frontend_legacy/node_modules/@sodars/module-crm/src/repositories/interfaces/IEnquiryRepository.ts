import { Enquiry } from '../../types';

export interface IEnquiryRepository {
  fetchEnquiries(): Promise<Enquiry[]>;
  findEnquiry(id: string): Promise<Enquiry | null>;
  saveEnquiry(enquiry: Enquiry): Promise<Enquiry>;
}
