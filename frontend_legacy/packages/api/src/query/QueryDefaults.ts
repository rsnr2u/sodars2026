export const QueryDefaults = {
  queries: {
    staleTime: 1000 * 60 * 5, // 5 minutes
    gcTime: 1000 * 60 * 10,  // 10 minutes
    retry: 2,
    refetchOnWindowFocus: false,
    refetchOnReconnect: true,
  },
  mutations: {
    retry: 1,
  }
};
export default QueryDefaults;
