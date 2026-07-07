declare global {
  interface Window {
    __SODARS_CONFIG__?: Record<string, string>;
  }
}

export const logger = {
  log: (msg: string, ...args: unknown[]) => console.log(`[SODARS] ${msg}`, ...args),
  warn: (msg: string, ...args: unknown[]) => console.warn(`[SODARS] ${msg}`, ...args),
  error: (msg: string, ...args: unknown[]) => console.error(`[SODARS] ${msg}`, ...args),
};

export const env = {
  get: (key: string, defaultVal: string = ''): string => {
    return window.__SODARS_CONFIG__?.[key] ?? (import.meta.env?.[`VITE_${key}`] as string) ?? defaultVal;
  }
};

export const formatMoney = (cents: number, currency: string = 'INR'): string => {
  return new Intl.NumberFormat('en-IN', {
    style: 'currency',
    currency,
  }).format(cents / 100);
};

export const formatDate = (dateStr: string): string => {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-IN', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};
