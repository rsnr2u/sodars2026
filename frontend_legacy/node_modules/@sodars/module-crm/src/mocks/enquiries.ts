import { Enquiry } from '../types';

export const mockEnquiries: Enquiry[] = [
  {
    id: 'enq_1',
    name: 'Acme Digital Media Deal',
    email: 'contact@acme.com',
    phone: '+1-555-0199',
    stage: 'New',
    source: 'Website',
    tags: ['hoarding', 'prime-location'],
    value: 12000,
    createdAt: Date.now() - 86400000 * 2,
    activities: [
      { id: 'act_1', enquiryId: 'enq_1', type: 'Note', details: 'Initial inbound contact request via site form.', timestamp: Date.now() - 86400000 * 2 }
    ],
    followUps: [
      { id: 'fup_1', enquiryId: 'enq_1', scheduledAt: Date.now() + 86400000, description: 'Follow up call on pricing proposal options.', isCompleted: false }
    ],
    attachments: []
  },
  {
    id: 'enq_2',
    name: 'Stark Industries Billboard Booking',
    email: 'info@stark.com',
    phone: '+1-555-0100',
    stage: 'Proposal',
    source: 'Referral',
    tags: ['digital-screen'],
    value: 45000,
    assignedTo: 'usr_1',
    createdAt: Date.now() - 86400000 * 10,
    activities: [
      { id: 'act_2', enquiryId: 'enq_2', type: 'Call', details: 'Stark assistant requested inventory quotes.', timestamp: Date.now() - 86400000 * 9 },
      { id: 'act_3', enquiryId: 'enq_2', type: 'Email', details: 'Pricing PDF catalog sent.', timestamp: Date.now() - 86400000 * 8 }
    ],
    followUps: [],
    attachments: []
  }
];
export default mockEnquiries;
