import { ProviderService } from '../services/ProviderService';
import { Provider } from '../types';
import { useState, useEffect } from 'react';

export const useProviders = () => {
  const [data, setData] = useState<Provider[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);

  const refresh = () => {
    setIsLoading(true);
    ProviderService.getProviders()
      .then(res => {
        setData(res);
        setIsLoading(false);
      })
      .catch(err => {
        setError(err instanceof Error ? err : new Error(String(err)));
        setIsLoading(false);
      });
  };

  const createProvider = async (provider: Provider) => {
    try {
      const created = await ProviderService.createProvider(provider);
      setData(prev => [...prev, created]);
      return created;
    } catch (err) {
      throw err instanceof Error ? err : new Error(String(err));
    }
  };

  const searchProviders = async (query: string) => {
    setIsLoading(true);
    try {
      const results = await ProviderService.searchProviders(query);
      setData(results);
      setIsLoading(false);
    } catch (err) {
      setError(err instanceof Error ? err : new Error(String(err)));
      setIsLoading(false);
    }
  };

  useEffect(() => {
    refresh();
  }, []);

  return {
    data,
    isLoading,
    error,
    refresh,
    createProvider,
    searchProviders,
  };
};
export default useProviders;
