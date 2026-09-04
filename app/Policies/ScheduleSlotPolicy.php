<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\ScheduleSlot;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ScheduleSlotPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ScheduleSlot $scheduleSlot): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ScheduleSlot $scheduleSlot): bool
    {
        return false;
    }

    public function updateAttendance(User $user, ScheduleSlot $slot): bool
    {

        dd([
        'user_id' => $user->id,
        'roles' => $user->getRoleNames(),
        'slot_id' => $slot->id,
        'slot_date' => $slot->date,
        'today' => now()->toDateString(),
        'is_today' => $slot->date->isToday(),
        'responsibles' => $slot->responsibles()->pluck('users.id')->toArray(),
        'is_responsible' => $slot->responsibles()
            ->where('users.id', $user->id)
            ->exists(),
    ]);
        if ($user->hasRole(Role::ADMIN, Role::RECEPTIONIST)) {
            return true;
        }

        if ($user->hasRole(Role::PROFESSOR)) {
            return $slot->date->isToday()
                && $slot->responsibles()
                ->where('users.id', $user->id)
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ScheduleSlot $scheduleSlot): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ScheduleSlot $scheduleSlot): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ScheduleSlot $scheduleSlot): bool
    {
        return false;
    }
}
