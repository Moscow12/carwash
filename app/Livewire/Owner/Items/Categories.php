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
        $this->ownerBusinesses = Auth::user()->ownedBusinesses()->orderBy('name')->get();
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

    public function openModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function editCategory($id)
    {
        $category = category::findOrFail($id);

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
        $this->categoryId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteCategory()
    {
        $category = category::findOrFail($this->categoryId);

        // Verify ownership
        $businessIds = Auth::user()->ownedBusinesses()->pluck('id');
        if (!$businessIds->contains($category->business_id)) {
            session()->flash('error', 'Unauthorized action.');
            $this->showDeleteModal = false;
            return;
        }

        $category->delete();
        session()->flash('message', 'Category deleted successfully.');
        $this->showDeleteModal = false;
        $this->categoryId = null;
    }

    public function save()
    {
        $this->validate();

        // Verify business ownership
        $businessIds = Auth::user()->ownedBusinesses()->pluck('id');
        if (!$businessIds->contains($this->business_id)) {
            session()->flash('error', 'Invalid business selected.');
            return;
        }

        $data = [
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'description' => $this->description ?: null,
            'status' => $this->status,
            'business_id' => $this->business_id,
        ];

        if ($this->editMode) {
            $category = category::findOrFail($this->categoryId);
            $category->update($data);
            session()->flash('message', 'Category updated successfully.');
        } else {
            category::create($data);
            session()->flash('message', 'Category created successfully.');
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
        $this->reset(['categoryId', 'name', 'description', 'status']);
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
        $businessIds = Auth::user()->ownedBusinesses()->pluck('id');

        $categories = category::whereIn('business_id', $businessIds)
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterBusiness, function ($query) {
                $query->where('business_id', $this->filterBusiness);
            })
            ->with('business')
            ->latest()
            ->paginate(10);

        return view('livewire.owner.items.categories', [
            'categories' => $categories
        ]);
    }
}
