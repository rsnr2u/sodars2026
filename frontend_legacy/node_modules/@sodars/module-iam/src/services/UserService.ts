import { UserRepository } from '../repositories/UserRepository';
import { UserSchema } from '../schemas/UserSchema';
import { UserTelemetry } from '../telemetry/UserTelemetry';
import { User } from '../types/user';

export class UserService {
  public static async getActiveUsers(): Promise<User[]> {
    const rawUsers = await UserRepository.fetchUsers();
    // 1. Run strict schema validation checks
    const validated = UserSchema.validateMany(rawUsers);
    // 2. Apply business filters
    return validated.filter(u => !u.isLocked);
  }

  public static async lockUserAccount(user: User): Promise<User> {
    const updatedUser: User = {
      ...user,
      isLocked: true
    };

    // 1. Validate updated user model
    const validated = UserSchema.validate(updatedUser);
    
    // 2. Track locked account metrics telemetry
    UserTelemetry.userLocked(validated);

    return validated;
  }
}
export default UserService;
