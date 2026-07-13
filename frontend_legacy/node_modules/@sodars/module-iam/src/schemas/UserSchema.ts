import { User } from '../types/user';

export class UserSchema {
  public static validate(user: any): User {
    if (!user || typeof user !== 'object') {
      throw new Error('[UserSchema] User payload must be an object.');
    }
    if (typeof user.id !== 'string') {
      throw new Error('[UserSchema] User id is missing or invalid.');
    }
    if (typeof user.name !== 'string') {
      throw new Error('[UserSchema] User name is missing or invalid.');
    }
    if (typeof user.email !== 'string' || !user.email.includes('@')) {
      throw new Error('[UserSchema] User email is missing or invalid.');
    }

    return {
      id: user.id,
      name: user.name,
      email: user.email,
      roleId: user.roleId || 'viewer',
      isLocked: !!user.isLocked
    };
  }

  public static validateMany(users: any[]): User[] {
    if (!Array.isArray(users)) {
      throw new Error('[UserSchema] Users payload must be an array.');
    }
    return users.map(u => this.validate(u));
  }
}
export default UserSchema;
