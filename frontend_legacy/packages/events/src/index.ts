export type AppEventCallback<T = any> = (payload: T) => void;

export class EventBus {
  private static listeners: Map<string, AppEventCallback[]> = new Map();

  public static subscribe<T = any>(event: string, callback: AppEventCallback<T>): () => void {
    const list = this.listeners.get(event) ?? [];
    list.push(callback);
    this.listeners.set(event, list);

    // Return unsubscribe trigger
    return () => {
      const current = this.listeners.get(event) ?? [];
      this.listeners.set(event, current.filter(cb => cb !== callback));
    };
  }

  public static publish<T = any>(event: string, payload: T): void {
    const callbacks = this.listeners.get(event) ?? [];
    for (const cb of callbacks) {
      try {
        cb(payload);
      } catch (err) {
        console.error(`[EventBus] Callback error handling event "${event}":`, err);
      }
    }
  }
}
