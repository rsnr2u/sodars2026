import { apiConfig } from './api';
import { authConfig } from './auth';
import { brandConfig } from './brand';
import { constantsConfig } from './constants';
import { environmentConfig } from './environment';
import { routingConfig } from './routing';
import { versionsConfig } from './versions';
import { featureFlagsConfig } from './featureFlags';
import { themeConfig } from './theme';

export const Config = {
  api: apiConfig,
  auth: authConfig,
  brand: brandConfig,
  constants: constantsConfig,
  environment: environmentConfig,
  routing: routingConfig,
  versions: versionsConfig,
  features: featureFlagsConfig,
  theme: themeConfig,
};

export default Config;
export * from './api';
export * from './auth';
export * from './brand';
export * from './constants';
export * from './environment';
export * from './routing';
export * from './versions';
export * from './featureFlags';
export * from './theme';
