import axios from 'axios';
import { useAuthStore, useTenantStore } from '@sodars/auth';

export const apiClient = axios.create({
  baseURL: '/api/v1',
  headers: {
    'Content-Type': 'application/json',
  },
});

apiClient.interceptors.request.use((config) => {
  const token = useAuthStore.getState().token;
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  const activeOrg = useTenantStore.getState().activeOrganization;
  if (activeOrg) {
    config.headers['X-Organization-Id'] = activeOrg.id;
  }

  return config;
}, (error) => {
  return Promise.reject(error);
});
