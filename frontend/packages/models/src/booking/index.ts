export interface Booking {
  id: string;
  campaignId: string;
  inventoryId: string;
  price: number;
  status: 'Pending' | 'Confirmed' | 'Cancelled';
}
