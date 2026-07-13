import { EnquiryService } from '../services/EnquiryService';
import { Enquiry } from '../types';
import { useState, useEffect } from 'react';

export const useEnquiries = () => {
  const [data, setData] = useState<Enquiry[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);

  const refresh = () => {
    setIsLoading(true);
    EnquiryService.getEnquiries()
      .then(res => {
        setData(res);
        setIsLoading(false);
      })
      .catch(err => {
        setError(err instanceof Error ? err : new Error(String(err)));
        setIsLoading(false);
      });
  };

  useEffect(() => {
    refresh();
  }, []);

  return { data, isLoading, error, refresh };
};
export default useEnquiries;
