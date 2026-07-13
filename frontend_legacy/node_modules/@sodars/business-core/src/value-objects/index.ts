export type CurrencyCode = 'USD' | 'INR' | 'EUR';

export interface Currency {
  readonly code: CurrencyCode;
  readonly symbol: string;
  readonly decimals: number;
}

export interface Money {
  readonly amount: number;
  readonly currency: CurrencyCode;
}

export interface ExchangeRate {
  readonly from: CurrencyCode;
  readonly to: CurrencyCode;
  readonly rate: number;
}

export interface GeoLocation {
  readonly latitude: number;
  readonly longitude: number;
}

export interface PhoneNumber {
  readonly countryCode: string;
  readonly number: string;
}

export interface EmailAddress {
  readonly value: string;
}

export interface Address {
  readonly street: string;
  readonly city: string;
  readonly state: string;
  readonly zipCode: string;
  readonly country: string;
  readonly isBilling: boolean;
}

export interface ContactInfo {
  readonly name: string;
  readonly email: string;
  readonly phone: string;
  readonly role: string;
}

export interface AttachmentReference {
  readonly id: string;
  readonly filename: string;
  readonly fileUrl: string;
  readonly mimeType?: string;
  readonly sizeBytes?: number;
}

export interface AuditInfo {
  readonly timestamp: number;
  readonly actorId: string;
  readonly action: string;
  readonly details: string;
}

export interface TimelineEvent<T = any> {
  readonly id: string;
  readonly type: string;
  readonly timestamp: number;
  readonly details: string;
  readonly metadata?: T;
}

export interface DateRange {
  readonly startDate: number;
  readonly endDate: number;
}

export interface BusinessHours {
  readonly dayOfWeek: number; // 0-6 Sun-Sat
  readonly openTime: string; // HH:MM
  readonly closeTime: string; // HH:MM
  readonly isClosed: boolean;
}

export interface Percentage {
  readonly value: number; // 0 to 100
}

export interface Dimension {
  readonly width: number;
  readonly height: number;
  readonly depth?: number;
  readonly unit: string;
}

export interface TimeRange {
  readonly startTime: string; // HH:MM
  readonly endTime: string; // HH:MM
}

export interface AuditTrail {
  readonly actorId: string;
  readonly timestamp: number;
  readonly action: string;
  readonly changes: Record<string, any>;
  readonly ipAddress?: string;
  readonly userAgent?: string;
}

export interface FileReference {
  readonly id: string;
  readonly filename: string;
  readonly storage: string;
  readonly path: string;
  readonly url: string;
  readonly mimeType: string;
  readonly size: number;
  readonly checksum?: string;
}

export interface PersonName {
  readonly firstName: string;
  readonly middleName?: string;
  readonly lastName: string;
}
