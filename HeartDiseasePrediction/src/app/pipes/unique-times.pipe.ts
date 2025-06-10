import { Pipe, PipeTransform } from '@angular/core';

interface ScheduleSlot {
    id?: number;
    day: string;
    time: string;
}

@Pipe({
    name: 'uniqueTimes',
    standalone: true
})
export class UniqueTimesPipe implements PipeTransform {
    transform(slots: ScheduleSlot[]): ScheduleSlot[] {
        if (!Array.isArray(slots)) return [];
        const uniqueTimes = new Set(slots.map(slot => slot.time));
        return Array.from(uniqueTimes)
            .sort()
            .map(time => ({ time } as ScheduleSlot));
    }
}