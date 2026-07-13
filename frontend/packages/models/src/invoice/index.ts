export interface Invoice {
  id: string;
  bookingId: string;
  amount: number;
  dueDate: string;
  status: 'Unpaid' | 'Paid' | 'Overdue';
}
