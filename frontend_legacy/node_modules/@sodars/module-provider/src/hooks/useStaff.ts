import { StaffService } from '../services/StaffService';
import { Staff } from '../types';
import { useState, useEffect } from 'react';

export const useStaff = (options?: { providerId?: string; branchId?: string }) => {
  const [data, setData] = useState<Staff[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);

  const providerId = options?.providerId;
  const branchId = options?.branchId;

  const refresh = () => {
    setIsLoading(true);
    let fetchPromise: Promise<Staff[]>;
    if (branchId) {
      fetchPromise = StaffService.getBranchStaff(branchId);
    } else {
      fetchPromise = StaffService.getStaff(providerId);
    }

    fetchPromise
      .then(res => {
        setData(res);
        setIsLoading(false);
      })
      .catch(err => {
        setError(err instanceof Error ? err : new Error(String(err)));
        setIsLoading(false);
      });
  };

  const createStaff = async (staff: Staff) => {
    try {
      const created = await StaffService.createStaff(staff);
      setData(prev => [...prev, created]);
      return created;
    } catch (err) {
      throw err instanceof Error ? err : new Error(String(err));
    }
  };

  const updateStaff = async (id: string, changes: Partial<Staff>) => {
    try {
      const updated = await StaffService.updateStaff(id, changes);
      setData(prev => prev.map(s => (s.id === id ? updated : s)));
      return updated;
    } catch (err) {
      throw err instanceof Error ? err : new Error(String(err));
    }
  };

  const deleteStaff = async (id: string) => {
    try {
      await StaffService.deleteStaff(id);
      setData(prev => prev.filter(s => s.id !== id));
    } catch (err) {
      throw err instanceof Error ? err : new Error(String(err));
    }
  };

  const assignToBranch = async (staffId: string, branchId: string) => {
    try {
      const updated = await StaffService.assignToBranch(staffId, branchId);
      setData(prev => prev.map(s => (s.id === staffId ? updated : s)));
      return updated;
    } catch (err) {
      throw err instanceof Error ? err : new Error(String(err));
    }
  };

  const transferBranch = async (staffId: string, newBranchId: string) => {
    try {
      const updated = await StaffService.transferBranch(staffId, newBranchId);
      setData(prev => prev.map(s => (s.id === staffId ? updated : s)));
      return updated;
    } catch (err) {
      throw err instanceof Error ? err : new Error(String(err));
    }
  };

  const activateStaff = async (staffId: string) => {
    try {
      await StaffService.activate(staffId);
      refresh();
    } catch (err) {
      throw err instanceof Error ? err : new Error(String(err));
    }
  };

  const deactivateStaff = async (staffId: string) => {
    try {
      await StaffService.deactivate(staffId);
      refresh();
    } catch (err) {
      throw err instanceof Error ? err : new Error(String(err));
    }
  };

  useEffect(() => {
    refresh();
  }, [providerId, branchId]);

  return {
    data,
    isLoading,
    error,
    refresh,
    createStaff,
    updateStaff,
    deleteStaff,
    assignToBranch,
    transferBranch,
    activateStaff,
    deactivateStaff,
  };
};
export default useStaff;
