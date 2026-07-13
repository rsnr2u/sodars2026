import React from 'react';

export const APP_NAME = 'SODAARS';
export const APP_SLOGAN = 'Enterprise Media ERP & Marketplace';

export const LogoSymbol: React.FC<React.SVGProps<SVGSVGElement>> = (props) => {
  return React.createElement(
    'svg',
    {
      viewBox: '0 0 100 100',
      fill: 'none',
      xmlns: 'http://www.w3.org/2000/svg',
      ...props,
    },
    React.createElement('path', {
      d: 'M50 10 L85 30 L85 70 L50 90 L15 70 L15 30 Z',
      stroke: 'currentColor',
      strokeWidth: '6',
      strokeLinejoin: 'round',
    }),
    React.createElement('path', {
      d: 'M50 25 L72 38 L72 62 L50 75 L28 62 L28 38 Z',
      fill: 'currentColor',
      opacity: '0.85',
    })
  );
};

export const LogoWordmark: React.FC<React.HTMLAttributes<HTMLSpanElement>> = ({ className = '', ...props }) => {
  return React.createElement(
    'span',
    {
      className: `font-heading text-lg font-extrabold tracking-wider select-none text-text-primary ${className}`,
      ...props,
    },
    'SODAARS'
  );
};

export const Logo: React.FC<{ className?: string }> = ({ className = '' }) => {
  return React.createElement(
    'div',
    { className: `flex items-center gap-2.5 ${className}` },
    React.createElement(LogoSymbol, { className: 'h-6 w-6 text-primary' }),
    React.createElement(LogoWordmark, null)
  );
};

export const setFavicon = (svgString?: string) => {
  if (typeof window === 'undefined') return;
  const defaultSvg = `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" fill="none">
      <path d="M50 10 L85 30 L85 70 L50 90 L15 70 L15 30 Z" stroke="#0B5D4B" stroke-width="8"/>
      <path d="M50 25 L72 38 L72 62 L50 75 L28 62 L28 38 Z" fill="#10B981"/>
    </svg>
  `;
  const blob = new Blob([svgString || defaultSvg], { type: 'image/svg+xml' });
  const link = (document.querySelector("link[rel*='icon']") || document.createElement('link')) as HTMLLinkElement;
  link.type = 'image/svg+xml';
  link.rel = 'shortcut icon';
  link.href = URL.createObjectURL(blob);
  document.getElementsByTagName('head')[0].appendChild(link);
};
