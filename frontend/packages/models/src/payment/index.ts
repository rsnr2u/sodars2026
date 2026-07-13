export interface Payment {
  id: string;
  invoiceId: string;
  amount: number;
  paymentDate: string;
  method: 'Bank Transfer' | 'Card' | 'UPI';
}
