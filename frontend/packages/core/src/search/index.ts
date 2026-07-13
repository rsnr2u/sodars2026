export interface SearchIndexItem {
  id: string;
  title: string;
  category: string;
  url: string;
}

export class SearchRegistry {
  private static index: SearchIndexItem[] = [];

  public static add(item: SearchIndexItem) {
    this.index.push(item);
  }

  public static search(query: string): SearchIndexItem[] {
    const q = query.toLowerCase();
    return this.index.filter(item => 
      item.title.toLowerCase().includes(q) || 
      item.category.toLowerCase().includes(q)
    );
  }
}
