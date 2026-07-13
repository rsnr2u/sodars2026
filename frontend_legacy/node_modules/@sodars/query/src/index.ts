import React from 'react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';

export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 5 * 60 * 1000, // 5 minutes default
      refetchOnWindowFocus: false,
      retry: 1,
    },
  },
});

export const QueryProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  return React.createElement(QueryClientProvider, { client: queryClient }, children);
};

// Global Query Keys Mappings
export const QueryKeys = {
  crm: {
    leads: () => ['crm', 'leads'] as const,
    lead: (id: string) => ['crm', 'leads', id] as const,
  },
  campaigns: {
    list: () => ['campaigns'] as const,
    detail: (id: string) => ['campaigns', id] as const,
  },
  wallet: {
    balance: () => ['wallet', 'balance'] as const,
    transactions: () => ['wallet', 'transactions'] as const,
  },
  operations: {
    schedules: () => ['operations', 'schedules'] as const,
    schedule: (id: string) => ['operations', 'schedules', id] as const,
    metrics: () => ['operations', 'metrics'] as const,
  }
};
