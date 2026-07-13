export interface RealtimeConnection {
  connect(): void;
  disconnect(): void;
  subscribe(channel: string, callback: (data: unknown) => void): () => void;
}

export class MockRealtimeConnection implements RealtimeConnection {
  public connect(): void {
    console.log('[Realtime] Connecting mock adapter endpoint stream channel...');
  }

  public disconnect(): void {
    console.log('[Realtime] Disconnecting mock adapter endpoint stream channel...');
  }

  public subscribe(channel: string, callback: (data: unknown) => void): () => void {
    console.log(`[Realtime] Subscribed to channel: ${channel}`);
    // Simulate heartbeats
    const interval = setInterval(() => {
      callback({
        event: 'heartbeat',
        channel,
        timestamp: new Date().toISOString(),
      });
    }, 15000);

    return () => {
      console.log(`[Realtime] Unsubscribed from channel: ${channel}`);
      clearInterval(interval);
    };
  }
}
