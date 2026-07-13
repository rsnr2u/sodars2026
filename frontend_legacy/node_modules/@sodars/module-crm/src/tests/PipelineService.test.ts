import { PipelineService } from '../services/PipelineService';

describe('PipelineService State Machine Tests', () => {
  it('should allow valid transitions', () => {
    const isValid = PipelineService.isValidTransition('New', 'Assigned');
    expect(isValid).toBe(true);
  });

  it('should block invalid transitions', () => {
    const isValid = PipelineService.isValidTransition('New', 'Won');
    expect(isValid).toBe(false);
  });

  it('should throw error on invalid transition validate call', () => {
    expect(() => {
      PipelineService.validateTransition('New', 'Won');
    }).toThrow();
  });
});
