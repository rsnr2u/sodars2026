// Placeholder for generic domain interfaces (e.g. specifications base interfaces)
export interface ISpecification<T> {
  isSatisfiedBy(candidate: T): boolean;
}
export interface IRepository<T> {
  findById(id: string): Promise<T | null>;
  save(entity: T): Promise<T>;
}
