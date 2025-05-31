<?php

namespace App\Observers;

use App\Models\Category;
use App\Models\CategoryLog;
use Illuminate\Support\Facades\Auth;

class CategoryObserver
{
    public function created(Category $category): void
    {
        CategoryLog::create([
            'category_id' => $category->id,
            'action' => 'created',
            'performed_by' => Auth::id(),
            'details' => json_encode($category->toArray()),
        ]);
    }

    public function updated(Category $category): void
    {
        CategoryLog::create([
            'category_id' => $category->id,
            'action' => 'updated',
            'performed_by' => Auth::id(),
            'details' => json_encode($category->getChanges()),
        ]);
    }

    public function deleted(Category $category): void
    {
        CategoryLog::create([
            'category_id' => $category->id,
            'action' => 'deleted',
            'performed_by' => Auth::id(),
            'details' => json_encode($category->toArray()),
        ]);
    }

    public function restored(Category $category): void
    {
        CategoryLog::create([
            'category_id' => $category->id,
            'action' => 'restored',
            'performed_by' => Auth::id(),
            'details' => json_encode($category->toArray()),
        ]);
    }

    public function forceDeleted(Category $category): void
    {
        CategoryLog::create([
            'category_id' => $category->id,
            'action' => 'force_deleted',
            'performed_by' => Auth::id(),
            'details' => json_encode($category->toArray()),
        ]);
    }
}
