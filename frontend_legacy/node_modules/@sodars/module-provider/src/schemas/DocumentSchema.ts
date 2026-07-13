import type { Document } from '../types';

export class DocumentSchema {
  public static validate(document: Document): Document {
    if (!document.id?.trim()) {
      throw new Error('Document ID is required.');
    }

    if (!document.providerId?.trim()) {
      throw new Error('Provider ID is required.');
    }

    if (!document.name?.trim()) {
      throw new Error('Document name is required.');
    }

    if (!document.type) {
      throw new Error('Document type is required.');
    }

    if (!document.file) {
      throw new Error('Document file is required.');
    }

    if (!document.file.id?.trim()) {
      throw new Error('Document file ID is required.');
    }

    if (!document.file.filename?.trim()) {
      throw new Error('Document filename is required.');
    }

    if (!document.file.fileUrl?.trim()) {
      throw new Error('Document file URL is required.');
    }

    return document;
  }

  public static validateMany(documents: Document[]): Document[] {
    return documents.map(document => this.validate(document));
  }
}
export default DocumentSchema;
