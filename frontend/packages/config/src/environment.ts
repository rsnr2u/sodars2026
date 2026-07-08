export const environmentConfig = {
  mode: process.env.NODE_ENV || 'development',
  isDev: process.env.NODE_ENV !== 'production',
  isProduction: process.env.NODE_ENV === 'production',
};
