import { SalesStage } from '../types';

export class PipelineService {
  private static allowedTransitions: Record<SalesStage, SalesStage[]> = {
    'New': ['Assigned', 'Lost', 'Archived'],
    'Assigned': ['Contacted', 'Lost', 'Archived'],
    'Contacted': ['Qualified', 'Lost', 'Archived'],
    'Qualified': ['Proposal', 'Lost', 'Archived'],
    'Proposal': ['Negotiation', 'Lost', 'Archived'],
    'Negotiation': ['Won', 'Lost', 'Archived'],
    'Won': ['Archived'],
    'Lost': ['Archived'],
    'Archived': []
  };

  public static isValidTransition(from: SalesStage, to: SalesStage): boolean {
    const list = this.allowedTransitions[from] || [];
    return list.includes(to);
  }

  public static validateTransition(from: SalesStage, to: SalesStage): void {
    if (!this.isValidTransition(from, to)) {
      throw new Error(`[PipelineService] Invalid state transition from "${from}" to "${to}".`);
    }
  }
}
export default PipelineService;
