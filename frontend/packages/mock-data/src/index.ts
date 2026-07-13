import { faker } from '@faker-js/faker';
import { Provider } from '@sodars/models';

const LOCAL_STORAGE_KEY = 'sodaars_mock_providers';

const INDIAN_CITIES_STATES = [
  { city: 'Mumbai', state: 'Maharashtra' },
  { city: 'Pune', state: 'Maharashtra' },
  { city: 'Nagpur', state: 'Maharashtra' },
  { city: 'Delhi', state: 'Delhi' },
  { city: 'Bangalore', state: 'Karnataka' },
  { city: 'Chennai', state: 'Tamil Nadu' },
  { city: 'Hyderabad', state: 'Telangana' },
  { city: 'Gurgaon', state: 'Haryana' },
  { city: 'Noida', state: 'Uttar Pradesh' },
  { city: 'Kolkata', state: 'West Bengal' },
  { city: 'Ahmedabad', state: 'Gujarat' },
  { city: 'Jaipur', state: 'Rajasthan' },
];

const AD_COMPANIES = [
  'Times OOH', 'Laqshya Media Group', 'Bright Outdoor Media', 'Signpost India',
  'Apex Media', 'Pioneer Publicity', 'Madison OOH', 'Active Media Solutions',
  'Milestone Brandcom', 'Graphis Ads', 'Lead OOH', 'Alakh Advertising',
  'Jagran Engage', 'Serve & Volley', 'Global Advertisers', 'Mera Hoarding',
  'Asian Ad Space', 'Skyline Media', 'In Outdoor Agencies', 'Blue Ocean Media',
  'Capital Outdoor', 'Metro Media Solutions', 'Vantage Advertising',
  'National Advertising Agency'
];

function generateGstin(stateCode: string, pan: string): string {
  return `${stateCode}${pan}1Z5`;
}

function generatePan(): string {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  const digits = '0123456789';
  let pan = '';
  for (let i = 0; i < 3; i++) pan += chars[Math.floor(Math.random() * chars.length)];
  pan += 'P'; // Personal/Proprietor status character
  pan += chars[Math.floor(Math.random() * chars.length)];
  for (let i = 0; i < 4; i++) pan += digits[Math.floor(Math.random() * digits.length)];
  pan += chars[Math.floor(Math.random() * chars.length)];
  return pan;
}

export const MockEngine = {
  getProviders(count = 500): Provider[] {
    if (typeof window !== 'undefined') {
      const cached = localStorage.getItem(LOCAL_STORAGE_KEY);
      if (cached) {
        try {
          return JSON.parse(cached);
        } catch (e) {
          console.error('Failed to parse cached mock providers', e);
        }
      }
    }

    // Generate deterministically using the specified seed
    faker.seed(2026);
    const providers: Provider[] = [];

    for (let i = 1; i <= count; i++) {
      const location = INDIAN_CITIES_STATES[i % INDIAN_CITIES_STATES.length];
      const baseName = AD_COMPANIES[i % AD_COMPANIES.length];
      const name = `${baseName} (${location.city})`;
      const pan = generatePan();
      const stateCode = '27'; // Maharashtra code for simplicity
      const gstNumber = generateGstin(stateCode, pan);
      const email = `contact@${baseName.toLowerCase().replace(/[^a-z0-9]/g, '')}.in`;
      const phone = `9${faker.string.numeric(9)}`;
      
      const status: Provider['status'] = i % 15 === 0 
        ? 'Suspended' 
        : i % 7 === 0 
          ? 'Under Review' 
          : i % 4 === 0 
            ? 'Pending' 
            : 'Verified';

      const complianceStatus: Provider['complianceStatus'] = i % 12 === 0
        ? 'Expired'
        : i % 5 === 0
          ? 'Pending'
          : 'Compliant';

      const createdDate = faker.date.between({ from: '2023-01-01', to: '2026-06-30' }).toISOString().split('T')[0];

      providers.push({
        id: `PRV-${1000 + i}`,
        name,
        email,
        phone,
        gstNumber,
        status,
        complianceStatus,
        city: location.city,
        state: location.state,
        createdDate,
        gst: { gstNumber, stateCode },
        branches: Array.from({ length: (i % 4) + 1 }).map((_, bIdx) => ({
          id: `BRN-${1000 + i}-${bIdx}`,
          providerId: `PRV-${1000 + i}`,
          name: `${location.city} Branch ${bIdx + 1}`,
          city: location.city,
          state: location.state,
          status: 'Active'
        })),
        staff: Array.from({ length: (i % 6) + 2 }).map((_, sIdx) => ({
          id: `STF-${1000 + i}-${sIdx}`,
          providerId: `PRV-${1000 + i}`,
          name: faker.person.fullName(),
          email: faker.internet.email(),
          phone: `9${faker.string.numeric(9)}`,
          role: sIdx === 0 ? 'Admin' : 'Operations',
          status: 'Active'
        })),
        agreements: Array.from({ length: (i % 3) + 1 }).map((_, aIdx) => ({
          id: `AGR-${1000 + i}-${aIdx}`,
          providerId: `PRV-${1000 + i}`,
          title: `SODAARS Media Lease Agreement v${aIdx + 1}`,
          status: aIdx === 0 ? 'Active' : 'Expired'
        })),
        verifications: [],
        documents: [],
        timeline: []
      });
    }

    if (typeof window !== 'undefined') {
      localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(providers));
    }

    return providers;
  },

  reset() {
    if (typeof window !== 'undefined') {
      localStorage.removeItem(LOCAL_STORAGE_KEY);
    }
  }
};
