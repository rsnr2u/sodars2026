import { VerificationService } from '../services/VerificationService';
import { Verification } from '../types';
import { useState, useEffect } from 'react';
import type { VerificationType } from '../enums';

export const useVerification = (providerId?: string) => {
  const [data, setData] = useState<Verification | null>(null);
  const [isLoading, setIsLoading] = useState(!!providerId);
  const [error, setError] = useState<Error | null>(null);

  const refresh = () => {
    if (!providerId) {
      setData(null);
      setIsLoading(false);
      return;
    }
    setIsLoading(true);
    VerificationService.getVerification(providerId)
      .then(res => {
        setData(res);
        setIsLoading(false);
      })
      .catch(err => {
        setError(err instanceof Error ? err : new Error(String(err)));
        setIsLoading(false);
      });
  };

  const startVerification = async (type: VerificationType) => {
    if (!providerId) throw new Error('No provider ID specified.');
    try {
      const result = await VerificationService.startVerification(providerId, type);
      setData(result);
      return result;
    } catch (err) {
      throw err instanceof Error ? err : new Error(String(err));
    }
  };

  const updateStepStatus = async (
    verificationId: string,
    stepId: string,
    status: any,
    notes?: string
  ) => {
    if (!providerId) throw new Error('No provider ID specified.');
    try {
      const result = await VerificationService.updateStepStatus(
        providerId,
        verificationId,
        stepId,
        status,
        notes
      );
      setData(result);
      return result;
    } catch (err) {
      throw err instanceof Error ? err : new Error(String(err));
    }
  };

  const approveVerification = async (verificationId: string) => {
    if (!providerId) throw new Error('No provider ID specified.');
    try {
      await VerificationService.approveVerification(providerId, verificationId);
      refresh();
    } catch (err) {
      throw err instanceof Error ? err : new Error(String(err));
    }
  };

  const rejectVerification = async (verificationId: string, reason: string) => {
    if (!providerId) throw new Error('No provider ID specified.');
    try {
      await VerificationService.rejectVerification(providerId, verificationId, reason);
      refresh();
    } catch (err) {
      throw err instanceof Error ? err : new Error(String(err));
    }
  };

  const restartVerification = async (verificationId: string) => {
    if (!providerId) throw new Error('No provider ID specified.');
    try {
      const result = await VerificationService.restartVerification(providerId, verificationId);
      setData(result);
      return result;
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
    startVerification,
    updateStepStatus,
    approveVerification,
    rejectVerification,
    restartVerification,
  };
};
export default useVerification;
