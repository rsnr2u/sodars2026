import { NavigationRegistry } from '@sodars/sdk';

export interface BreadcrumbNode {
  title: string;
  route: string;
}

export const useBreadcrumbs = (pathname: string): BreadcrumbNode[] => {
  const flatList = NavigationRegistry.getFlatList();
  const segments = pathname.split('/').filter(Boolean);
  
  const breadcrumbs: BreadcrumbNode[] = [{ title: 'Home', route: '/' }];
  let currentPath = '';

  for (const segment of segments) {
    currentPath += `/${segment}`;
    const matchedNode = flatList.find(node => node.route === currentPath);
    if (matchedNode) {
      breadcrumbs.push({
        title: matchedNode.title,
        route: matchedNode.route || currentPath,
      });
    } else {
      breadcrumbs.push({
        title: segment.charAt(0).toUpperCase() + segment.slice(1),
        route: currentPath,
      });
    }
  }

  return breadcrumbs;
};
