import { BranchService } from '../services/BranchService';
import { Branch } from '../types';
import { useState, useEffect } from 'react';

export const useBranch = (id?: string) => {
  const [data, setData] = useState<Branch | null>(null);
  const [isLoading, setIsLoading] = useState(!!id);
  const [error, setError] = useState<Error | null>(null);

  const refresh = () => {
    if (!id) {
      setData(null);
      setIsLoading(false);
      return;
    }
    setIsLoading(true);
    BranchService.getBranch(id)
      .then(res => {
        setData(res);
        setIsLoading(false);
      })
      .catch(err => {
        setError(err instanceof Error ? err : new Error(String(err)));
        setIsLoading(false);
      });
  };

  const updateBranch = async (changes: Partial<Branch>) => {
    if (!id) throw new Error('No branch ID specified.');
    try {
      const updated = await BranchService.updateBranch(id, changes);
      setData(updated);
      return updated;
    } catch (err) {
      throw err instanceof Error ? err : new Error(String(err));
    }
  };

  const deleteBranch = async () => {
    if (!id) throw new Error('No branch ID specified.');
    try {
      await BranchService.deleteBranch(id);
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
    updateBranch,
    deleteBranch,
  };
};
export default useBranch;
