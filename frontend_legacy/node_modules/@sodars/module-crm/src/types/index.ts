// CRM sales stage workflow statuses
export type SalesStage = 'New' | 'Assigned' | 'Contacted' | 'Qualified' | 'Proposal' | 'Negotiation' | 'Won' | 'Lost' | 'Archived';

// Activity timeline types
export type ActivityType = 'Call' | 'Meeting' | 'Email' | 'WhatsApp' | 'SMS' | 'Note' | 'Task' | 'Reminder' | 'Visit' | 'Document';

// Value Object: Contact Details
export interface Contact {
  readonly name: string;
  readonly email: string;
  readonly phone: string;
  readonly role?: string;
}

// Value Object: Address Coordinates
export interface Address {
  readonly street: string;
  readonly city: string;
  readonly state: string;
  readonly zipCode: string;
  readonly country: string;
  readonly isBilling: boolean;
}

// Entity: Customer Activity timeline event details
export interface TimelineEvent {
  readonly id: string;
  readonly type: string;
  readonly timestamp: number;
  readonly details: string;
}

// Aggregate Root: Customer Profile
export interface Customer {
  readonly id: string;
  readonly companyName: string;
  readonly email: string;
  readonly phone: string;
  readonly contacts: Contact[];
  readonly addresses: Address[];
  readonly timeline: TimelineEvent[];
  readonly createdAt: number;
}

// Entity: Follow Up Logs Action callback details
export interface FollowUp {
  readonly id: string;
  readonly enquiryId: string;
  readonly scheduledAt: number;
  readonly description: string;
  readonly isCompleted: boolean;
}

// Entity: Timeline Activity note/email logs details
export interface Activity {
  readonly id: string;
  readonly enquiryId: string;
  readonly type: ActivityType;
  readonly details: string;
  readonly timestamp: number;
}

// Entity: Tag categorization
export interface Tag {
  readonly id: string;
  readonly label: string;
}

// Entity: Lead Attachment files meta
export interface Attachment {
  readonly id: string;
  readonly filename: string;
  readonly fileUrl: string;
}

// Value Object: QuoteItem line elements
export interface QuoteItem {
  readonly description: string;
  readonly quantity: number;
  readonly unitPrice: number;
}

// Aggregate Root: Quotation estimates details
export interface Quotation {
  readonly id: string;
  readonly customerId: string;
  readonly items: QuoteItem[];
  readonly totalAmount: number;
  readonly status: 'Draft' | 'Sent' | 'Accepted' | 'Declined';
  readonly expiresAt: number;
}

// Aggregate Root: Enquiry Lead profile details
export interface Enquiry {
  readonly id: string;
  readonly name: string;
  readonly email: string;
  readonly phone: string;
  readonly stage: SalesStage;
  readonly source: string;
  readonly campaignSource?: string;
  readonly tags: string[];
  readonly value: number;
  readonly assignedTo?: string;
  readonly activities: Activity[];
  readonly followUps: FollowUp[];
  readonly attachments: Attachment[];
  readonly createdAt: number;
}

// Productivity Tasks
export interface Task {
  readonly id: string;
  readonly title: string;
  readonly priority: 'Low' | 'Medium' | 'High';
  readonly isCompleted: boolean;
  readonly dueDate: number;
}
