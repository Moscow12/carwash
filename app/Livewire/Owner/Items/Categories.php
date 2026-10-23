<?php

namespace App\Livewire\Owner\Items;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\category;
use App\Models\Business;

#[Layout('components.layouts.app-owner')]
class Categories extends Component
{
    use WithPagination;

    public $search = '';
    public $filterBusiness = '';
    public $statusFilter = '';
    public $sortBy = 'name';
    public $sortDirection = 'asc';
    public $addAnother = false;

    /** @var array{name: string, items_count: int}|null shown in the delete confirmation */
    public $deletingCategory = null;
    public $showModal = false;
    public $showDeleteModal = false;
    public $editMode = false;
    public $categoryId = null;

    #[Rule('required|string|max:255')]
    public $name = '';

    #[Rule('nullable|string|max:500')]
    public $description = '';

    #[Rule('required|in:active,inactive')]
    public $status = 'active';

    #[Rule('required|exists:businesses,id')]
    public $business_id = '';

    public $ownerBusinesses = [];
    public $hasBusinesses = false;

    public function mount()
    {
        $this->ownerBusinesses = Auth::user()->assignedBusinesses()->orderBy('name')->get();
        $this->hasBusinesses = $this->ownerBusinesses->count() > 0;

        if ($this->ownerBusinesses->count() === 1) {
            $this->business_id = $this->ownerBusinesses->first()->id;
            $this->filterBusiness = $this->business_id;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterBusiness()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    /**
     * Sort by name, item count or date added; clicking the active column again flips the direction.
     */
    public function sortTable(string $field): void
    {
        $defaults = ['name' => 'asc', 'items_count' => 'desc', 'created_at' => 'desc'];

        if (! array_key_exists($field, $defaults)) {
            return;
        }

        $this->sortDirection = $this->sortBy === $field
            ? ($this->sortDirection === 'asc' ? 'desc' : 'asc')
            : $defaults[$field];
        $this->sortBy = $field;
        $this->resetPage();
    }

    /**
     * Find a category that belongs to one of the user's businesses.
     */
    protected function findOwnedCategory($id): ?category
    {
        return category::whereKey($id)
            ->whereIn('business_id', Auth::user()->assignedBusinesses()->pluck('id'))
            ->first();
    }

    public function toggleStatus($id): void
    {
        $category = $this->findOwnedCategory($id);

        if (! $category) {
            session()->flash('error', 'Category not found.');
            return;
        }

        $category->update(['status' => $category->status === 'active' ? 'inactive' : 'active']);
        session()->flash('message', "Category \"{$category->name}\" is now {$category->status}.");
    }

    public function openModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function editCategory($id)
    {
        $category = $this->findOwnedCategory($id);

        if (! $category) {
            session()->flash('error', 'Category not found.');
            return;
        }

        $this->resetValidation();
        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description ?? '';
        $this->status = $category->status;
        $this->business_id = $category->business_id;

        $this->editMode = true;
        $this->showModal = true;
    }

    public function confirmDelete($id)
    {
        $category = $this->findOwnedCategory($id);

        if (! $category) {
            session()->flash('error', 'Category not found.');
            return;
        }

        $this->categoryId = $category->id;
        $this->deletingCategory = ['name' => $category->name, 'items_count' => $category->items()->count()];
        $this->showDeleteModal = true;
    }

    public function deleteCategory()
    {
        $category = $this->findOwnedCategory($this->categoryId);

        if (! $category) {
            session()->flash('error', 'Unauthorized action.');
            $this->closeDeleteModal();
            return;
        }

        // items.category_id cascades on delete, so removing a category in use would silently delete its items
        $itemsCount = $category->items()->count();
        if ($itemsCount > 0) {
            session()->flash('error', "\"{$category->name}\" still has {$itemsCount} ".Str::plural('item', $itemsCount).'. Move them to another category or delete them first.');
            $this->closeDeleteModal();
            return;
        }

        $category->delete();
        session()->flash('message', 'Category deleted successfully.');
        $this->showDeleteModal = false;
        $this->categoryId = null;
        $this->deletingCategory = null;
    }

    public function save()
    {
        $this->validate();

        $duplicate = category::where('business_id', $this->business_id)
            ->whereRaw('LOWER(name) = ?', [Str::lower(trim($this->name))])
            ->when($this->editMode, fn ($query) => $query->whereKeyNot($this->categoryId))
            ->exists();

        if ($duplicate) {
            $this->addError('name', 'A category with this name already exists in this business.');
            return;
        }

        // Verify business ownership
        $businessIds = Auth::user()->assignedBusinesses()->pluck('id');
        if (!$businessIds->contains($this->business_id)) {
            session()->flash('error', 'Invalid business selected.');
            return;
        }

        $data = [
            'name' => trim($this->name),
            'slug' => Str::slug($this->name),
            'description' => $this->description ?: null,
            'status' => $this->status,
            'business_id' => $this->business_id,
        ];

        if ($this->editMode) {
            $category = $this->findOwnedCategory($this->categoryId);

            if (! $category) {
                session()->flash('error', 'Category not found.');
                $this->closeModal();
                return;
            }

            $category->update($data);
            session()->flash('message', 'Category updated successfully.');
        } else {
            category::create($data);
            session()->flash('message', "Category \"{$data['name']}\" created.");

            if ($this->addAnother) {
                $this->reset(['name', 'description']);
                $this->resetValidation();
                $this->dispatch('category-form-reset');
                return;
            }
        }

        $this->closeModal();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->categoryId = null;
    }

    public function resetForm()
    {
        $this->reset(['categoryId', 'name', 'description', 'status', 'addAnother']);
        $this->status = 'active';

        if ($this->ownerBusinesses->count() === 1) {
            $this->business_id = $this->ownerBusinesses->first()->id;
        } else {
            $this->business_id = $this->filterBusiness ?: '';
        }

        $this->resetValidation();
    }

    public function render()
    {
        $businessIds = Auth::user()->assignedBusinesses()->pluck('id');

        $scoped = category::whereIn('business_id', $businessIds)
            ->when($this->filterBusiness, function ($query) {
                $query->where('business_id', $this->filterBusiness);
            });

        $sortBy = in_array($this->sortBy, ['name', 'items_count', 'created_at'], true) ? $this->sortBy : 'name';

        $categories = (clone $scoped)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
            ->with('business')
            ->withCount('items')
            ->orderBy($sortBy, $this->sortDirection === 'desc' ? 'desc' : 'asc')
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.owner.items.categories', [
            'categories' => $categories,
            'stats' => [
                'total' => (clone $scoped)->count(),
                'active' => (clone $scoped)->where('status', 'active')->count(),
                'inactive' => (clone $scoped)->where('status', 'inactive')->count(),
                'empty' => (clone $scoped)->doesntHave('items')->count(),
            ],
        ]);
    }
}
