export interface Campaign {
  id: string;
  name: string;
  customerId: string;
  budget: number;
  startDate: string;
  endDate: string;
  status: 'Draft' | 'Active' | 'Completed';
}
