import { NavigationRegistry } from '@sodars/sdk';
import { PROVIDER_MODULE_ID } from '../constants/module';

export const registerNavigation = (navigation: typeof NavigationRegistry): void => {
  navigation.register({
    id: 'provider',
    module: PROVIDER_MODULE_ID,
    title: 'Provider Manager',
    icon: 'provider',
    order: 50,
  });

  navigation.register({
    id: 'provider.dashboard',
    module: PROVIDER_MODULE_ID,
    title: 'Dashboard',
    parent: 'provider',
    route: '/provider',
    order: 5,
  });

  navigation.register({
    id: 'provider.leads',
    module: PROVIDER_MODULE_ID,
    title: 'Lead Management',
    parent: 'provider',
    order: 10,
  });

  navigation.register({
    id: 'provider.providers',
    module: PROVIDER_MODULE_ID,
    title: 'All Providers',
    parent: 'provider.leads',
    route: '/providers',
    order: 5,
  });

  navigation.register({
    id: 'provider.branches_parent',
    module: PROVIDER_MODULE_ID,
    title: 'Branch Management',
    parent: 'provider',
    order: 20,
  });

  navigation.register({
    id: 'provider.branches',
    module: PROVIDER_MODULE_ID,
    title: 'Branches Directory',
    parent: 'provider.branches_parent',
    route: '/providers',
    order: 5,
  });

  navigation.register({
    id: 'provider.staff_parent',
    module: PROVIDER_MODULE_ID,
    title: 'Staff Management',
    parent: 'provider',
    order: 30,
  });

  navigation.register({
    id: 'provider.staff',
    module: PROVIDER_MODULE_ID,
    title: 'Staff Directory',
    parent: 'provider.staff_parent',
    route: '/providers',
    order: 5,
  });
};
export default registerNavigation;
