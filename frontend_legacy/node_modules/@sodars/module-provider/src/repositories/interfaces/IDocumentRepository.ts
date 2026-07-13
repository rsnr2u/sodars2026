import { Document } from '../../types';

export interface IDocumentRepository {
  fetchDocuments(providerId?: string): Promise<Document[]>;
  saveDocument(document: Document): Promise<Document>;
}
export default IDocumentRepository;
