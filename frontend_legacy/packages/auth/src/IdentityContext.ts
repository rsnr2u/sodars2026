import { createContext, useContext } from 'react';
import { IdentityFacade, identity } from './Identity';

export const IdentityContext = createContext<IdentityFacade>(identity);

export const useIdentity = () => {
  return useContext(IdentityContext);
};
