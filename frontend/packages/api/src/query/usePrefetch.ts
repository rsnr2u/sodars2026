export const usePrefetch = () => {
  return async (queryKey: string[], fetcher: () => Promise<unknown>) => {
    console.log(`[QueryCache] Prefetching query key:`, queryKey);
    try {
      const data = await fetcher();
      return data;
    } catch (err) {
      console.error(`[QueryCache] Prefetch failed for:`, queryKey, err);
      return null;
    }
  };
};
export default usePrefetch;
