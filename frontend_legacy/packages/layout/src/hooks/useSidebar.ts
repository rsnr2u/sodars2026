import { useShell } from '../providers/ShellProvider';

export const useSidebar = () => {
  const { sidebarOpen, toggleSidebar, setSidebarOpen, mobileDrawerOpen, setMobileDrawerOpen } = useShell();
  return {
    sidebarOpen,
    toggleSidebar,
    setSidebarOpen,
    mobileDrawerOpen,
    setMobileDrawerOpen,
  };
};
