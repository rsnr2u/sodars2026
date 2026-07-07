import { InternalAxiosRequestConfig } from 'axios';
import { ApiMiddleware } from './ApiMiddleware';
import { useTenantStore } from '@sodars/store';

export class TenantMiddleware implements ApiMiddleware {
  public async execute(config: InternalAxiosRequestConfig): Promise<InternalAxiosRequestConfig> {
    const { activeOrganization, activeBranch, activeWorkspace } = useTenantStore.getState();

    if (activeOrganization) {
      config.headers['X-Organization-Id'] = activeOrganization.id;
    }
    if (activeBranch) {
      config.headers['X-Branch-Id'] = activeBranch.id;
    }
    if (activeWorkspace) {
      config.headers['X-Workspace-Id'] = activeWorkspace.id;
    }

    return config;
  }
}
