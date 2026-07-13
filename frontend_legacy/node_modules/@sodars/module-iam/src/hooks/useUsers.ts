import { UserService } from '../services/UserService';
import { User } from '../types/user';
import { useState, useEffect } from 'react';

export const useUsers = () => {
  const [data, setData] = useState<User[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<Error | null>(null);

  useEffect(() => {
    UserService.getActiveUsers()
      .then(res => {
        setData(res);
        setIsLoading(false);
      })
      .catch(err => {
        setError(err instanceof Error ? err : new Error(String(err)));
        setIsLoading(false);
      });
  }, []);

  return { data, isLoading, error };
};
export default useUsers;
