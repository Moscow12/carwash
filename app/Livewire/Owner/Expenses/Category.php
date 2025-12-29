<?php

namespace App\Livewire\Owner\Expenses;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\expense_category;

#[Layout('components.layouts.app-owner')]
class Category extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 25;
    public $selectedCarwash = '';

    // Modal state
    public $showModal = false;
    public $editingId = null;

    // Form fields
    public $name = '';
    public $code = '';
    public $isSubcategory = false;
    public $parent_id = '';
    public $status = 'active';

    // Stats
    public $totalCategories = 0;
    public $parentCount = 0;
    public $subcategoryCount = 0;

    public function mount()
    {
        $firstCarwash = Auth::user()->ownedCarwashes()->first();
        if ($firstCarwash) {
            $this->selectedCarwash = $firstCarwash->id;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedCarwash()
    {
        $this->resetPage();
        $this->loadStats();
    }

    public function updatedIsSubcategory()
    {
        if (!$this->isSubcategory) {
            $this->parent_id = '';
        }
    }

    public function loadStats()
    {
        if (!$this->selectedCarwash) {
            $this->totalCategories = 0;
            $this->parentCount = 0;
            $this->subcategoryCount = 0;
            return;
        }

        $baseQuery = expense_category::where('carwash_id', $this->selectedCarwash);
        $this->totalCategories = (clone $baseQuery)->count();
        $this->parentCount = (clone $baseQuery)->parentCategories()->count();
        $this->subcategoryCount = (clone $baseQuery)->subCategories()->count();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->editingId = null;
        $this->name = '';
        $this->code = '';
        $this->isSubcategory = false;
        $this->parent_id = '';
        $this->status = 'active';
        $this->resetValidation();
    }

    public function edit($id)
    {
        $category = expense_category::find($id);
        if ($category) {
            $this->editingId = $id;
            $this->name = $category->name;
            $this->code = $category->code ?? '';
            $this->isSubcategory = !is_null($category->parent_id);
            $this->parent_id = $category->parent_id ?? '';
            $this->status = $category->status;
            $this->showModal = true;
        }
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|min:2',
            'code' => 'nullable|string|max:50',
        ]);

        if (!$this->selectedCarwash) {
            session()->flash('error', 'Please select a carwash first.');
            return;
        }

        try {
            $data = [
                'name' => $this->name,
                'code' => $this->code ?: null,
                'parent_id' => $this->isSubcategory && $this->parent_id ? $this->parent_id : null,
                'carwash_id' => $this->selectedCarwash,
                'status' => $this->status,
            ];

            if ($this->editingId) {
                $category = expense_category::find($this->editingId);
                if ($category) {
                    // Prevent setting parent to self or own children
                    if ($this->isSubcategory && $this->parent_id === $this->editingId) {
                        session()->flash('error', 'Category cannot be its own parent.');
                        return;
                    }
                    $category->update($data);
                    session()->flash('message', 'Category updated successfully.');
                }
            } else {
                expense_category::create($data);
                session()->flash('message', 'Category added successfully.');
            }

            $this->closeModal();
            $this->loadStats();
        } catch (\Exception $e) {
            session()->flash('error', 'Error saving category: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $category = expense_category::find($id);
            if ($category) {
                // Check if has children
                if ($category->children()->count() > 0) {
                    session()->flash('error', 'Cannot delete category with sub-categories. Delete sub-categories first.');
                    return;
                }
                // Check if has expenses
                if ($category->expenses()->count() > 0) {
                    session()->flash('error', 'Cannot delete category with expenses. Remove or reassign expenses first.');
                    return;
                }
                $category->delete();
                $this->loadStats();
                session()->flash('message', 'Category deleted successfully.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Cannot delete category. It may have associated records.');
        }
    }

    public function getParentCategories()
    {
        if (!$this->selectedCarwash) {
            return collect();
        }

        $query = expense_category::where('carwash_id', $this->selectedCarwash)
            ->parentCategories()
            ->orderBy('name');

        // Exclude current category when editing
        if ($this->editingId) {
            $query->where('id', '!=', $this->editingId);
        }

        return $query->get();
    }

    public function render()
    {
        $this->loadStats();

        $carwashes = Auth::user()->ownedCarwashes()->orderBy('name')->get();

        // Get parent categories with their children
        $categories = collect();

        if ($this->selectedCarwash) {
            $parentCategories = expense_category::where('carwash_id', $this->selectedCarwash)
                ->parentCategories()
                ->when($this->search, function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('code', 'like', "%{$this->search}%");
                })
                ->with('children')
                ->orderBy('name')
                ->get();

            // Flatten the hierarchy for display
            foreach ($parentCategories as $parent) {
                $categories->push($parent);
                foreach ($parent->children as $child) {
                    $categories->push($child);
                }
            }

            // Also get orphan subcategories that match search
            if ($this->search) {
                $orphanMatches = expense_category::where('carwash_id', $this->selectedCarwash)
                    ->subCategories()
                    ->where(function ($q) {
                        $q->where('name', 'like', "%{$this->search}%")
                            ->orWhere('code', 'like', "%{$this->search}%");
                    })
                    ->whereNotIn('id', $categories->pluck('id'))
                    ->get();

                foreach ($orphanMatches as $orphan) {
                    $categories->push($orphan);
                }
            }
        }

        // Manual pagination
        $page = $this->getPage();
        $total = $categories->count();
        $paginatedCategories = $categories->slice(($page - 1) * $this->perPage, $this->perPage)->values();

        return view('livewire.owner.expenses.category', [
            'categories' => $paginatedCategories,
            'carwashes' => $carwashes,
            'parentCategoriesList' => $this->getParentCategories(),
            'total' => $total,
            'from' => $total > 0 ? (($page - 1) * $this->perPage) + 1 : 0,
            'to' => min($page * $this->perPage, $total),
            'lastPage' => ceil($total / $this->perPage) ?: 1,
            'currentPage' => $page,
        ]);
    }
}
