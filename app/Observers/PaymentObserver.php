<?php

namespace App\Observers;

use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentObserver
{
    public function created(Payment $payment)
    {
        $this->log('created', $payment);
    }

    public function updated(Payment $payment)
    {
        $this->log('updated', $payment);
    }

    public function deleted(Payment $payment)
    {
        $this->log('deleted', $payment);
    }

    protected function log($action, Payment $payment)
    {
        DB::table('payment_logs')->insert([
            'payment_id' => $payment->id,
            'action' => $action,
            'performed_by' => Auth::id() ?? 0,
            'details' => json_encode($payment->toArray(), JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
        ]);
    }
}
