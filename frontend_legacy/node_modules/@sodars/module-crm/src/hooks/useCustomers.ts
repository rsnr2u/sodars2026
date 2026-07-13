import { CustomerService } from '../services/CustomerService';
import { Customer } from '../types';
import { useState, useEffect } from 'react';

export const useCustomers = () => {
  const [data, setData] = useState<Customer[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);

  const refresh = () => {
    setIsLoading(true);
    CustomerService.getCustomers()
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
export default useCustomers;
