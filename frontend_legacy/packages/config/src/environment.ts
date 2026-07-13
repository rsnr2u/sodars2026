const getEnvMode = () => {
  try {
    return (import.meta as any).env?.MODE || 'development';
  } catch {
    return 'development';
  }
};

const mode = getEnvMode();

export const environmentConfig = {
  mode,
  isDev: mode !== 'production',
  isProduction: mode === 'production',
};
