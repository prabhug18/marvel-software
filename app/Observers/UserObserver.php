<?php

namespace App\Observers;

use App\Models\User;
use App\Models\UserLog;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        //       
         UserLog::create([
            'user_id'   => $user->id,
            'action'        => 'created',
            'performed_by'  => Auth::id(),
            'details'   => json_encode($user->toArray()),
        ]);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
        UserLog::create([
            'user_id' => $user->id,
            'action'      => 'updated',
            'performed_by'=> Auth::id(),
            'details'     => json_encode($user->getChanges()),
        ]);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
        
        UserLog::create([
            'user_id' => $user->id,
            'action'      => 'deleted',
            'performed_by'=> Auth::id(),
            'details'     => json_encode($user->toArray()),
        ]);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
        UserLog::create([
            'user_id' => $user->id,
            'action'      => 'restored',
            'performed_by'=> Auth::id(),
            'details'     => json_encode($user->toArray()),
        ]);
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
        UserLog::create([
            'user_id' => $user->id,
            'action'      => 'force_deleted',
            'performed_by'=> Auth::id(),
            'details'     => json_encode($user->toArray()),
        ]);
    }
}
