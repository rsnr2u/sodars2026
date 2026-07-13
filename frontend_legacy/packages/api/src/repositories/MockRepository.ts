import { BaseRepository } from './BaseRepository';

export abstract class MockRepository<TEntity extends { id: string }>
  extends BaseRepository<TEntity> {

  protected constructor(
    protected readonly items: TEntity[],
  ) {
    super();
  }

  async findAll(): Promise<TEntity[]> {
    return [...this.items];
  }

  async findById(id: string): Promise<TEntity | null> {
    return this.items.find(item => item.id === id) ?? null;
  }

  async create(entity: TEntity): Promise<TEntity> {
    this.items.push(entity);
    return entity;
  }

  async update(
    id: string,
    changes: Partial<TEntity>,
  ): Promise<TEntity> {

    const entity = await this.findById(id);

    if (!entity) {
      throw new Error(`Entity '${id}' not found.`);
    }

    Object.assign(entity, changes);

    return entity;
  }

  async delete(id: string): Promise<void> {
    const index = this.items.findIndex(item => item.id === id);

    if (index >= 0) {
      this.items.splice(index, 1);
    }
  }

  protected filter(
    predicate: (item: TEntity) => boolean,
  ): TEntity[] {
    return this.items.filter(predicate);
  }

  protected first(
    predicate: (item: TEntity) => boolean,
  ): TEntity | null {
    return this.items.find(predicate) ?? null;
  }

  protected existsWhere(
    predicate: (item: TEntity) => boolean,
  ): boolean {
    return this.items.some(predicate);
  }
}
export default MockRepository;
