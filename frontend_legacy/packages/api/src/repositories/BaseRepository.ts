export abstract class BaseRepository<TEntity, TId = string> {
  abstract findAll(): Promise<TEntity[]>;
  abstract findById(id: TId): Promise<TEntity | null>;
  abstract create(entity: TEntity): Promise<TEntity>;
  abstract update(id: TId, entity: Partial<TEntity>): Promise<TEntity>;
  abstract delete(id: TId): Promise<void>;
}
export default BaseRepository;
