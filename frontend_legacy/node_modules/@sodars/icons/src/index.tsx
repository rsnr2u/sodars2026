import React from 'react';
import * as Lucide from 'lucide-react';

export type IconType =
  | 'dashboard'
  | 'crm'
  | 'campaign'
  | 'inventory'
  | 'provider'
  | 'wallet'
  | 'finance'
  | 'transport'
  | 'operations'
  | 'analytics'
  | 'audit'
  | 'settings';

interface SodarsIconProps extends React.SVGProps<SVGSVGElement> {
  name: IconType;
  className?: string;
  size?: number;
}

export const SodarsIcon: React.FC<SodarsIconProps> = ({
  name,
  className = '',
  size = 18,
  ...props
}) => {
  const iconMap: Record<IconType, React.ComponentType<any>> = {
    dashboard: Lucide.LayoutDashboard,
    crm: Lucide.Users,
    campaign: Lucide.Megaphone,
    inventory: Lucide.Package,
    provider: Lucide.Building2,
    wallet: Lucide.Wallet,
    finance: Lucide.CircleDollarSign,
    transport: Lucide.Truck,
    operations: Lucide.Calendar,
    analytics: Lucide.BarChart3,
    audit: Lucide.FileSearch,
    settings: Lucide.Settings,
  };

  const IconComponent = iconMap[name];

  if (!IconComponent) {
    console.warn(`[SodarsIcon] Icon name "${name}" is not registered in the mapping.`);
    return React.createElement(Lucide.HelpCircle, {
      className,
      size,
      ...props
    });
  }

  return React.createElement(IconComponent, {
    className,
    size,
    ...props
  });
};
