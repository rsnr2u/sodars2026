import { ProviderService } from '../services/ProviderService';
import { Provider } from '../types';
import { useState, useEffect } from 'react';

export const useProvider = (id?: string) => {
  const [data, setData] = useState<Provider | null>(null);
  const [isLoading, setIsLoading] = useState(!!id);
  const [error, setError] = useState<Error | null>(null);

  const refresh = () => {
    if (!id) {
      setData(null);
      setIsLoading(false);
      return;
    }
    setIsLoading(true);
    ProviderService.getProvider(id)
      .then(res => {
        setData(res);
        setIsLoading(false);
      })
      .catch(err => {
        setError(err instanceof Error ? err : new Error(String(err)));
        setIsLoading(false);
      });
  };

  const updateProvider = async (changes: Partial<Provider>) => {
    if (!id) throw new Error('No provider ID specified.');
    try {
      const updated = await ProviderService.updateProvider(id, changes);
      setData(updated);
      return updated;
    } catch (err) {
      throw err instanceof Error ? err : new Error(String(err));
    }
  };

  const deleteProvider = async () => {
    if (!id) throw new Error('No provider ID specified.');
    try {
      await ProviderService.deleteProvider(id);
      setData(null);
    } catch (err) {
      throw err instanceof Error ? err : new Error(String(err));
    }
  };

  useEffect(() => {
    refresh();
  }, [id]);

  return {
    data,
    isLoading,
    error,
    refresh,
    updateProvider,
    deleteProvider,
  };
};
export default useProvider;
