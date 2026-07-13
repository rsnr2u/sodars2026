import { BranchService } from '../services/BranchService';
import { Branch } from '../types';
import { useState, useEffect } from 'react';

export const useBranches = (providerId?: string) => {
  const [data, setData] = useState<Branch[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);

  const refresh = () => {
    setIsLoading(true);
    BranchService.getBranches(providerId)
      .then(res => {
        setData(res);
        setIsLoading(false);
      })
      .catch(err => {
        setError(err instanceof Error ? err : new Error(String(err)));
        setIsLoading(false);
      });
  };

  const createBranch = async (branch: Branch) => {
    try {
      const created = await BranchService.createBranch(branch);
      setData(prev => [...prev, created]);
      return created;
    } catch (err) {
      throw err instanceof Error ? err : new Error(String(err));
    }
  };

  const deleteBranch = async (id: string) => {
    try {
      await BranchService.deleteBranch(id);
      setData(prev => prev.filter(b => b.id !== id));
    } catch (err) {
      throw err instanceof Error ? err : new Error(String(err));
    }
  };

  useEffect(() => {
    refresh();
  }, [providerId]);

  return {
    data,
    isLoading,
    error,
    refresh,
    createBranch,
    deleteBranch,
  };
};
export default useBranches;
