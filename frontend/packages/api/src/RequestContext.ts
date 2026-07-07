export interface RequestContextData {
  organizationId: string | null;
  branchId: string | null;
  workspaceId: string | null;
  deviceId: string | null;
  clientVersion: string;
  platformVersion: string;
  timezone: string;
  locale: string;
}

export class RequestContext {
  private data: RequestContextData;

  private constructor(data: RequestContextData) {
    this.data = data;
  }

  public get organizationId() { return this.data.organizationId; }
  public get branchId() { return this.data.branchId; }
  public get workspaceId() { return this.data.workspaceId; }
  public get deviceId() { return this.data.deviceId; }
  public get clientVersion() { return this.data.clientVersion; }
  public get platformVersion() { return this.data.platformVersion; }
  public get timezone() { return this.data.timezone; }
  public get locale() { return this.data.locale; }

  public clone(): RequestContext {
    return new RequestContext({ ...this.data });
  }

  public static builder(): RequestContextBuilder {
    return new RequestContextBuilder();
  }
}

class RequestContextBuilder {
  private data: RequestContextData = {
    organizationId: null,
    branchId: null,
    workspaceId: null,
    deviceId: 'web-client-device-id',
    clientVersion: '1.1.1',
    platformVersion: '1.0.0',
    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
    locale: navigator.language || 'en-US',
  };

  public organization(id: string | null): RequestContextBuilder {
    this.data.organizationId = id;
    return this;
  }

  public branch(id: string | null): RequestContextBuilder {
    this.data.branchId = id;
    return this;
  }

  public workspace(id: string | null): RequestContextBuilder {
    this.data.workspaceId = id;
    return this;
  }

  public locale(locale: string): RequestContextBuilder {
    this.data.locale = locale;
    return this;
  }

  public timezone(timezone: string): RequestContextBuilder {
    this.data.timezone = timezone;
    return this;
  }

  public build(): RequestContext {
    return new (RequestContext as any)(this.data);
  }
}
