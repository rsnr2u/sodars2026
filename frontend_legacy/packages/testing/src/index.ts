import { UserDTO, OrganizationDTO } from '@sodars/contracts';

export const mockUser: UserDTO = {
  id: 'usr-999-id',
  name: 'Test Administrator',
  email: 'admin@sodars.com',
  roles: ['super_admin'],
  permissions: ['*'],
};

export const mockOrganization: OrganizationDTO = {
  id: 'org-999-id',
  name: 'Sodars Operations Corp',
  slug: 'sodars-ops',
};
