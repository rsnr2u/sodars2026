export class PermissionRegistry {
  private static userPermissions = new Set<string>();

  public static setPermissions(permissions: string[]) {
    this.userPermissions = new Set(permissions);
  }

  public static has(permission: string): boolean {
    return this.userPermissions.has(permission);
  }
}
