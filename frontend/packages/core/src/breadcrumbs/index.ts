export interface BreadcrumbItem {
  label: string;
  href?: string;
}

export class BreadcrumbRegistry {
  private static items: BreadcrumbItem[] = [];

  public static set(crumbs: BreadcrumbItem[]) {
    this.items = crumbs;
  }

  public static get(): BreadcrumbItem[] {
    return this.items;
  }
}
