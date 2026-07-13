import { TimelineEvent } from '@sodars/business-core';

export interface IProviderTimelineRepository {
  fetchTimeline(providerId: string): Promise<TimelineEvent[]>;
  saveTimelineEvent(providerId: string, event: TimelineEvent): Promise<TimelineEvent>;
}
export default IProviderTimelineRepository;
