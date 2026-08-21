<?php

namespace App\Data\ScheduleSlot;

class ScheduleSlotCalendarData
{
        public function __construct(
                public readonly int $id,
                public readonly string $startTime,
                public readonly string $endTime,
        ) {}
}
