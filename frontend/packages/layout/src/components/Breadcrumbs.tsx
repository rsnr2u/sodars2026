import React from 'react';
import { useBreadcrumbs } from '../hooks/useBreadcrumbs';

export const Breadcrumbs: React.FC = () => {
  const pathname = window.location.pathname;
  const list = useBreadcrumbs(pathname);

  return (
    <nav className="flex items-center space-x-1.5 text-xs text-text-secondary select-none py-2 px-1">
      {list.map((node, index) => {
        const isLast = index === list.length - 1;
        return (
          <React.Fragment key={node.route + index}>
            {index > 0 && <span className="text-text-muted">/</span>}
            {isLast ? (
              <span className="font-semibold text-text-primary">{node.title}</span>
            ) : (
              <a href={node.route} className="hover:text-text-primary transition-colors">
                {node.title}
              </a>
            )}
          </React.Fragment>
        );
      })}
    </nav>
  );
};
export default Breadcrumbs;
