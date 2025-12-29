<?php

namespace App\Livewire\Owner\Expenses;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\expenses as ExpenseModel;
use App\Models\expense_category;
use App\Models\staffs;
use App\Models\suplier;
use Carbon\Carbon;

#[Layout('components.layouts.app-owner')]
class Expenses extends Component
{
    use WithPagination;

    // Filters
    public $selectedCarwash = '';
    public $categoryFilter = '';
    public $subcategoryFilter = '';
    public $paymentStatusFilter = '';
    public $expenseForFilter = '';
    public $contactFilter = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $search = '';
    public $perPage = 25;

    // Modal state
    public $showModal = false;
    public $editingId = null;

    // Form fields
    public $expense_date = '';
    public $reference_no = '';
    public $category_id = '';
    public $subcategory_id = '';
    public $total_amount = '';
    public $tax_amount = 0;
    public $payment_status = 'pending';
    public $expense_for = '';
    public $expense_for_id = '';
    public $contact = '';
    public $contact_id = '';
    public $expense_note = '';
    public $is_recurring = false;
    public $recurring_interval = '';
    public $recurring_count = '';

    // Stats
    public $totalExpenses = 0;
    public $totalAmount = 0;
    public $totalPaid = 0;
    public $totalPending = 0;

    public function mount()
    {
        $firstCarwash = Auth::user()->ownedCarwashes()->first();
        if ($firstCarwash) {
            $this->selectedCarwash = $firstCarwash->id;
        }

        // Set default date range to current year
        $this->dateFrom = Carbon::now()->startOfYear()->format('Y-m-d');
        $this->dateTo = Carbon::now()->endOfYear()->format('Y-m-d');
        $this->expense_date = Carbon::now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedCarwash()
    {
        $this->resetPage();
        $this->categoryFilter = '';
        $this->subcategoryFilter = '';
        $this->loadStats();
    }

    public function updatedCategoryFilter()
    {
        $this->subcategoryFilter = '';
        $this->resetPage();
    }

    public function updatedIsRecurring()
    {
        if (!$this->is_recurring) {
            $this->recurring_interval = '';
            $this->recurring_count = '';
        }
    }

    public function loadStats()
    {
        if (!$this->selectedCarwash) {
            $this->totalExpenses = 0;
            $this->totalAmount = 0;
            $this->totalPaid = 0;
            $this->totalPending = 0;
            return;
        }

        $baseQuery = ExpenseModel::where('carwash_id', $this->selectedCarwash);

        if ($this->dateFrom && $this->dateTo) {
            $baseQuery->inDateRange($this->dateFrom, $this->dateTo);
        }

        $this->totalExpenses = (clone $baseQuery)->count();
        $this->totalAmount = (clone $baseQuery)->sum('total_amount');
        $this->totalPaid = (clone $baseQuery)->paid()->sum('total_amount');
        $this->totalPending = (clone $baseQuery)->where('payment_status', '!=', 'paid')->sum('payment_due');
    }

    public function openModal()
    {
        $this->resetForm();
        $this->expense_date = Carbon::now()->format('Y-m-d');
        if ($this->selectedCarwash) {
            $this->reference_no = ExpenseModel::generateReferenceNo($this->selectedCarwash);
        }
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
        $this->expense_date = Carbon::now()->format('Y-m-d');
        $this->reference_no = '';
        $this->category_id = '';
        $this->subcategory_id = '';
        $this->total_amount = '';
        $this->tax_amount = 0;
        $this->payment_status = 'pending';
        $this->expense_for = '';
        $this->expense_for_id = '';
        $this->contact = '';
        $this->contact_id = '';
        $this->expense_note = '';
        $this->is_recurring = false;
        $this->recurring_interval = '';
        $this->recurring_count = '';
        $this->resetValidation();
    }

    public function edit($id)
    {
        $expense = ExpenseModel::find($id);
        if ($expense) {
            $this->editingId = $id;
            $this->expense_date = $expense->expense_date->format('Y-m-d');
            $this->reference_no = $expense->reference_no;
            $this->category_id = $expense->category_id ?? '';
            $this->subcategory_id = $expense->subcategory_id ?? '';
            $this->total_amount = $expense->total_amount;
            $this->tax_amount = $expense->tax_amount;
            $this->payment_status = $expense->payment_status;
            $this->expense_for = $expense->expense_for ?? '';
            $this->expense_for_id = $expense->expense_for_id ?? '';
            $this->contact = $expense->contact ?? '';
            $this->contact_id = $expense->contact_id ?? '';
            $this->expense_note = $expense->expense_note ?? '';
            $this->is_recurring = $expense->is_recurring;
            $this->recurring_interval = $expense->recurring_interval ?? '';
            $this->recurring_count = $expense->recurring_count ?? '';
            $this->showModal = true;
        }
    }

    public function save()
    {
        $this->validate([
            'expense_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'payment_status' => 'required|in:pending,partial,paid',
        ]);

        if (!$this->selectedCarwash) {
            session()->flash('error', 'Please select a carwash first.');
            return;
        }

        try {
            $paymentDue = $this->payment_status === 'paid' ? 0 : $this->total_amount;
            if ($this->payment_status === 'partial') {
                $paymentDue = $this->total_amount / 2; // Default to half for partial
            }

            $data = [
                'expense_date' => $this->expense_date,
                'reference_no' => $this->reference_no ?: ExpenseModel::generateReferenceNo($this->selectedCarwash),
                'carwash_id' => $this->selectedCarwash,
                'category_id' => $this->category_id ?: null,
                'subcategory_id' => $this->subcategory_id ?: null,
                'total_amount' => $this->total_amount,
                'tax_amount' => $this->tax_amount ?: 0,
                'payment_due' => $paymentDue,
                'payment_status' => $this->payment_status,
                'expense_for' => $this->expense_for ?: null,
                'expense_for_id' => $this->expense_for_id ?: null,
                'contact' => $this->contact ?: null,
                'contact_id' => $this->contact_id ?: null,
                'expense_note' => $this->expense_note ?: null,
                'is_recurring' => $this->is_recurring,
                'recurring_interval' => $this->is_recurring ? $this->recurring_interval : null,
                'recurring_count' => $this->is_recurring ? $this->recurring_count : null,
            ];

            if ($this->editingId) {
                $expense = ExpenseModel::find($this->editingId);
                if ($expense) {
                    $expense->update($data);
                    session()->flash('message', 'Expense updated successfully.');
                }
            } else {
                $data['added_by'] = Auth::id();
                ExpenseModel::create($data);
                session()->flash('message', 'Expense added successfully.');
            }

            $this->closeModal();
            $this->loadStats();
        } catch (\Exception $e) {
            session()->flash('error', 'Error saving expense: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $expense = ExpenseModel::find($id);
            if ($expense) {
                $expense->delete();
                $this->loadStats();
                session()->flash('message', 'Expense deleted successfully.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting expense: ' . $e->getMessage());
        }
    }

    public function markAsPaid($id)
    {
        $expense = ExpenseModel::find($id);
        if ($expense) {
            $expense->update([
                'payment_status' => 'paid',
                'payment_due' => 0,
            ]);
            $this->loadStats();
            session()->flash('message', 'Expense marked as paid.');
        }
    }

    public function getCategories()
    {
        if (!$this->selectedCarwash) {
            return collect();
        }

        return expense_category::where('carwash_id', $this->selectedCarwash)
            ->parentCategories()
            ->active()
            ->orderBy('name')
            ->get();
    }

    public function getSubcategories($parentId = null)
    {
        if (!$this->selectedCarwash) {
            return collect();
        }

        $query = expense_category::where('carwash_id', $this->selectedCarwash)
            ->subCategories()
            ->active()
            ->orderBy('name');

        if ($parentId) {
            $query->where('parent_id', $parentId);
        }

        return $query->get();
    }

    public function getStaffs()
    {
        if (!$this->selectedCarwash) {
            return collect();
        }

        return staffs::where('carwash_id', $this->selectedCarwash)
            ->active()
            ->orderBy('name')
            ->get();
    }

    public function getSuppliers()
    {
        return suplier::active()->orderBy('name')->get();
    }

    public function render()
    {
        $this->loadStats();

        $carwashes = Auth::user()->ownedCarwashes()->orderBy('name')->get();

        $expenses = collect();
        $paidCount = 0;
        $grandTotal = 0;
        $grandPaymentDue = 0;

        if ($this->selectedCarwash) {
            $query = ExpenseModel::where('carwash_id', $this->selectedCarwash)
                ->when($this->categoryFilter, fn($q) => $q->where('category_id', $this->categoryFilter))
                ->when($this->subcategoryFilter, fn($q) => $q->where('subcategory_id', $this->subcategoryFilter))
                ->when($this->paymentStatusFilter, fn($q) => $q->where('payment_status', $this->paymentStatusFilter))
                ->when($this->expenseForFilter, fn($q) => $q->where('expense_for_id', $this->expenseForFilter))
                ->when($this->contactFilter, fn($q) => $q->where('contact_id', $this->contactFilter))
                ->when($this->dateFrom && $this->dateTo, fn($q) => $q->inDateRange($this->dateFrom, $this->dateTo))
                ->when($this->search, function ($q) {
                    $q->where(function ($query) {
                        $query->where('reference_no', 'like', "%{$this->search}%")
                            ->orWhere('expense_note', 'like', "%{$this->search}%")
                            ->orWhere('expense_for', 'like', "%{$this->search}%")
                            ->orWhere('contact', 'like', "%{$this->search}%");
                    });
                })
                ->with(['category', 'subcategory', 'carwash', 'addedByUser', 'expenseForStaff', 'contactSupplier'])
                ->orderBy('expense_date', 'desc')
                ->orderBy('created_at', 'desc');

            // Calculate totals before pagination
            $paidCount = (clone $query)->paid()->count();
            $grandTotal = (clone $query)->sum('total_amount');
            $grandPaymentDue = (clone $query)->sum('payment_due');

            $expenses = $query->paginate($this->perPage);
        }

        return view('livewire.owner.expenses.expenses', [
            'expenses' => $expenses,
            'carwashes' => $carwashes,
            'categories' => $this->getCategories(),
            'subcategories' => $this->getSubcategories($this->categoryFilter ?: $this->category_id),
            'staffs' => $this->getStaffs(),
            'suppliers' => $this->getSuppliers(),
            'paidCount' => $paidCount,
            'grandTotal' => $grandTotal,
            'grandPaymentDue' => $grandPaymentDue,
        ]);
    }
}
