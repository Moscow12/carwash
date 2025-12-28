<?php

namespace App\Livewire\Owner\Items;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\category;
use App\Models\carwashes;

#[Layout('components.layouts.app-owner')]
class Categories extends Component
{
    use WithPagination;

    public $search = '';
    public $filterCarwash = '';
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

    #[Rule('required|exists:carwashes,id')]
    public $carwash_id = '';

    public $ownerCarwashes = [];

    public function mount()
    {
        $this->ownerCarwashes = Auth::user()->ownedCarwashes()->orderBy('name')->get();

        if ($this->ownerCarwashes->count() === 1) {
            $this->carwash_id = $this->ownerCarwashes->first()->id;
            $this->filterCarwash = $this->carwash_id;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterCarwash()
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
        $this->carwash_id = $category->carwash_id;

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
        $carwashIds = Auth::user()->ownedCarwashes()->pluck('id');
        if (!$carwashIds->contains($category->carwash_id)) {
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

        // Verify carwash ownership
        $carwashIds = Auth::user()->ownedCarwashes()->pluck('id');
        if (!$carwashIds->contains($this->carwash_id)) {
            session()->flash('error', 'Invalid carwash selected.');
            return;
        }

        $data = [
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'description' => $this->description ?: null,
            'status' => $this->status,
            'carwash_id' => $this->carwash_id,
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

        if ($this->ownerCarwashes->count() === 1) {
            $this->carwash_id = $this->ownerCarwashes->first()->id;
        } else {
            $this->carwash_id = $this->filterCarwash ?: '';
        }

        $this->resetValidation();
    }

    public function render()
    {
        $carwashIds = Auth::user()->ownedCarwashes()->pluck('id');

        $categories = category::whereIn('carwash_id', $carwashIds)
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterCarwash, function ($query) {
                $query->where('carwash_id', $this->filterCarwash);
            })
            ->with('carwash')
            ->latest()
            ->paginate(10);

        return view('livewire.owner.items.categories', [
            'categories' => $categories
        ]);
    }
}
