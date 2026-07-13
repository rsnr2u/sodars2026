export interface NotificationItem {
  id: string;
  title: string;
  body: string;
  timestamp: number;
  read: boolean;
}

export class NotificationRegistry {
  private static list: NotificationItem[] = [];

  public static add(title: string, body: string) {
    this.list.unshift({
      id: `notify-${Date.now()}-${Math.random()}`,
      title,
      body,
      timestamp: Date.now(),
      read: false
    });
  }

  public static getUnreadCount(): number {
    return this.list.filter(n => !n.read).length;
  }

  public static getAll(): NotificationItem[] {
    return this.list;
  }
}
