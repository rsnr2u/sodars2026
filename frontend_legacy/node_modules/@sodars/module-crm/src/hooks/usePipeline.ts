import { EnquiryService } from '../services/EnquiryService';
import { SalesStage } from '../types';
import { useState } from 'react';

export const usePipeline = () => {
  const [isUpdating, setIsUpdating] = useState(false);
  const [updateError, setUpdateError] = useState<Error | null>(null);

  const transitionLead = async (enquiryId: string, stage: SalesStage) => {
    setIsUpdating(true);
    setUpdateError(null);
    try {
      const result = await EnquiryService.updateStage(enquiryId, stage);
      setIsUpdating(false);
      return result;
    } catch (err) {
      const error = err instanceof Error ? err : new Error(String(err));
      setUpdateError(error);
      setIsUpdating(false);
      throw error;
    }
  };

  return { transitionLead, isUpdating, updateError };
};
export default usePipeline;
