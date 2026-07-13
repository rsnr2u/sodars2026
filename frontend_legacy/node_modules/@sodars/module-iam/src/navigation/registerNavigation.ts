import { NavigationRegistry } from '@sodars/sdk';
import { IAM_MODULE_ID } from '../constants/module';

export const registerNavigation = (navigation: typeof NavigationRegistry): void => {
  navigation.register({
    id: 'iam',
    module: IAM_MODULE_ID,
    title: 'Access Control',
    icon: 'settings',
    order: 80,
  });

  navigation.register({
    id: 'iam.dashboard',
    module: IAM_MODULE_ID,
    title: 'IAM Dashboard',
    parent: 'iam',
    route: '/iam',
    order: 5,
  });

  navigation.register({
    id: 'iam.users',
    module: IAM_MODULE_ID,
    title: 'Users Directory',
    parent: 'iam',
    route: '/iam/users',
    order: 10,
  });

  navigation.register({
    id: 'iam.roles',
    module: IAM_MODULE_ID,
    title: 'Roles & Policies',
    parent: 'iam',
    route: '/iam/roles',
    order: 20,
  });

  navigation.register({
    id: 'iam.permissions',
    module: IAM_MODULE_ID,
    title: 'Permissions Matrix',
    parent: 'iam',
    route: '/iam/permissions',
    order: 30,
  });

  navigation.register({
    id: 'iam.sessions',
    module: IAM_MODULE_ID,
    title: 'Active Sessions',
    parent: 'iam',
    route: '/iam/sessions',
    order: 40,
  });

  navigation.register({
    id: 'iam.audit',
    module: IAM_MODULE_ID,
    title: 'Audit Logs',
    parent: 'iam',
    route: '/iam/audit',
    order: 50,
  });

  navigation.register({
    id: 'iam.tokens',
    module: IAM_MODULE_ID,
    title: 'API Tokens',
    parent: 'iam',
    route: '/iam/tokens',
    order: 60,
  });
};
export default registerNavigation;
