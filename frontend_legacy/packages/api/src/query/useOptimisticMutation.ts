export const useOptimisticMutation = <TVariables, TData>(
  mutationFn: (variables: TVariables) => Promise<TData>,
  onMutate: (variables: TVariables) => void,
  onError: (error: Error, variables: TVariables) => void,
  onSuccess: (data: TData, variables: TVariables) => void
) => {
  return async (variables: TVariables): Promise<TData> => {
    onMutate(variables);
    try {
      const result = await mutationFn(variables);
      onSuccess(result, variables);
      return result;
    } catch (err: any) {
      onError(err instanceof Error ? err : new Error(String(err)), variables);
      throw err;
    }
  };
};
export default useOptimisticMutation;
