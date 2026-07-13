export const useInvalidate = () => {
  return async (queryKey: string[]) => {
    console.log(`[QueryCache] Invalidating query key:`, queryKey);
    // Invalidate triggers stub
    return Promise.resolve();
  };
};
export default useInvalidate;
