import { GSTRegistration } from '../../types';

export interface IGSTRepository {
  findGST(providerId: string): Promise<GSTRegistration | null>;
  saveGST(gst: GSTRegistration): Promise<GSTRegistration>;
}
export default IGSTRepository;
