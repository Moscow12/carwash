<div class="p-6">
    {{-- Header --}}
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Stock Transfers</h2>
            <p class="text-sm text-gray-600">Manage inter-outlet inventory transfers</p>
        </div>
        <button wire:click="createTransfer" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            New Transfer
        </button>
    </div>

    {{-- Alerts --}}
    @if (session()->has('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Filters --}}
    <div class="mb-6 bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search Reference</label>
                <input type="text" wire:model.live="searchTerm" placeholder="ST-000001" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select wire:model.live="statusFilter" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="all">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="requested">Requested</option>
                    <option value="approved">Approved</option>
                    <option value="dispatched">Dispatched</option>
                    <option value="received">Received</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From Outlet</label>
                <select wire:model.live="fromOutletFilter" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="all">All Outlets</option>
                    @foreach($outlets as $outlet)
                        <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To Outlet</label>
                <select wire:model.live="toOutletFilter" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="all">All Outlets</option>
                    @foreach($outlets as $outlet)
                        <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Transfers List --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">From</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">To</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($transfers as $transfer)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $transfer->reference_no }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $transfer->fromOutlet->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $transfer->toOutlet->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $transfer->items->count() }} items
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($transfer->status === 'draft') bg-gray-100 text-gray-800
                                @elseif($transfer->status === 'requested') bg-yellow-100 text-yellow-800
                                @elseif($transfer->status === 'approved') bg-blue-100 text-blue-800
                                @elseif($transfer->status === 'dispatched') bg-purple-100 text-purple-800
                                @elseif($transfer->status === 'received') bg-green-100 text-green-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($transfer->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $transfer->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <button wire:click="viewTransfer('{{ $transfer->id }}')" class="text-blue-600 hover:text-blue-900">View</button>

                            @if($transfer->status === 'draft' || $transfer->status === 'requested')
                                <button wire:click="approveTransfer('{{ $transfer->id }}')" class="text-green-600 hover:text-green-900">Approve</button>
                            @endif

                            @if($transfer->status === 'approved')
                                <button wire:click="dispatchTransfer('{{ $transfer->id }}')" class="text-purple-600 hover:text-purple-900">Dispatch</button>
                            @endif

                            @if($transfer->status === 'dispatched')
                                <button wire:click="showReceiveModal('{{ $transfer->id }}')" class="text-indigo-600 hover:text-indigo-900">Receive</button>
                            @endif

                            @if($transfer->status !== 'received')
                                <button wire:click="cancelTransfer('{{ $transfer->id }}')" class="text-red-600 hover:text-red-900">Cancel</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                            No stock transfers found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-6 py-4">
            {{ $transfers->links() }}
        </div>
    </div>

    {{-- Modal --}}
    @if($showModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50" wire:click.self="closeModal">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto m-4">

                {{-- Create Transfer Modal --}}
                @if($viewMode === 'create')
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-bold text-gray-900">Create Stock Transfer</h3>
                            <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">From Outlet *</label>
                                    <select wire:model="from_outlet_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Select Outlet</option>
                                        @foreach($outlets as $outlet)
                                            <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('from_outlet_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">To Outlet *</label>
                                    <select wire:model="to_outlet_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Select Outlet</option>
                                        @foreach($outlets as $outlet)
                                            <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('to_outlet_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea wire:model="notes" rows="2" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                            </div>

                            <div class="border-t pt-4">
                                <div class="flex justify-between items-center mb-3">
                                    <h4 class="font-medium text-gray-900">Transfer Items</h4>
                                    <button wire:click="addItem" type="button" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                                        + Add Item
                                    </button>
                                </div>

                                <div class="space-y-3">
                                    @foreach($transferItems as $index => $item)
                                        <div class="border rounded p-3 bg-gray-50">
                                            <div class="grid grid-cols-12 gap-3">
                                                <div class="col-span-5">
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Item *</label>
                                                    <select wire:model="transferItems.{{ $index }}.item_id" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                                        <option value="">Select Item</option>
                                                        @foreach($items as $itemOption)
                                                            <option value="{{ $itemOption->id }}">{{ $itemOption->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-span-2">
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Quantity *</label>
                                                    <input type="number" step="0.001" wire:model="transferItems.{{ $index }}.quantity_sent" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                                </div>

                                                <div class="col-span-2">
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Unit</label>
                                                    <select wire:model="transferItems.{{ $index }}.unit_id" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                                        <option value="">Select</option>
                                                        @foreach($units as $unit)
                                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-span-2">
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Notes</label>
                                                    <input type="text" wire:model="transferItems.{{ $index }}.notes" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                                                </div>

                                                <div class="col-span-1 flex items-end">
                                                    <button wire:click="removeItem({{ $index }})" type="button" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-sm">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="flex justify-end gap-3 border-t pt-4">
                                <button wire:click="closeModal" type="button" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button wire:click="saveTransfer" type="button" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                                    Create Transfer
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- View Transfer Modal --}}
                @if($viewMode === 'view' && $selectedTransfer)
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-bold text-gray-900">Transfer Details</h3>
                            <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded">
                                <div>
                                    <span class="text-sm text-gray-600">Reference:</span>
                                    <p class="font-medium">{{ $selectedTransfer->reference_no }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-600">Status:</span>
                                    <p class="font-medium">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($selectedTransfer->status === 'draft') bg-gray-100 text-gray-800
                                            @elseif($selectedTransfer->status === 'requested') bg-yellow-100 text-yellow-800
                                            @elseif($selectedTransfer->status === 'approved') bg-blue-100 text-blue-800
                                            @elseif($selectedTransfer->status === 'dispatched') bg-purple-100 text-purple-800
                                            @elseif($selectedTransfer->status === 'received') bg-green-100 text-green-800
                                            @else bg-red-100 text-red-800
                                            @endif">
                                            {{ ucfirst($selectedTransfer->status) }}
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-600">From Outlet:</span>
                                    <p class="font-medium">{{ $selectedTransfer->fromOutlet->name ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-600">To Outlet:</span>
                                    <p class="font-medium">{{ $selectedTransfer->toOutlet->name ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-600">Requested By:</span>
                                    <p class="font-medium">{{ $selectedTransfer->requestedBy->name ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-600">Created:</span>
                                    <p class="font-medium">{{ $selectedTransfer->created_at->format('M d, Y H:i') }}</p>
                                </div>
                                @if($selectedTransfer->approved_by)
                                    <div>
                                        <span class="text-sm text-gray-600">Approved By:</span>
                                        <p class="font-medium">{{ $selectedTransfer->approvedBy->name ?? 'N/A' }}</p>
                                    </div>
                                @endif
                                @if($selectedTransfer->dispatched_at)
                                    <div>
                                        <span class="text-sm text-gray-600">Dispatched At:</span>
                                        <p class="font-medium">{{ $selectedTransfer->dispatched_at->format('M d, Y H:i') }}</p>
                                    </div>
                                @endif
                                @if($selectedTransfer->received_at)
                                    <div>
                                        <span class="text-sm text-gray-600">Received At:</span>
                                        <p class="font-medium">{{ $selectedTransfer->received_at->format('M d, Y H:i') }}</p>
                                    </div>
                                @endif
                            </div>

                            @if($selectedTransfer->notes)
                                <div class="bg-gray-50 p-4 rounded">
                                    <span class="text-sm text-gray-600">Notes:</span>
                                    <p class="font-medium">{{ $selectedTransfer->notes }}</p>
                                </div>
                            @endif

                            <div class="border-t pt-4">
                                <h4 class="font-medium text-gray-900 mb-3">Transfer Items</h4>
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Item</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Qty Sent</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Qty Received</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Unit</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($selectedTransfer->items as $item)
                                            <tr>
                                                <td class="px-4 py-2 text-sm">{{ $item->item->name ?? 'N/A' }}</td>
                                                <td class="px-4 py-2 text-sm">{{ number_format($item->quantity_sent, 3) }}</td>
                                                <td class="px-4 py-2 text-sm">{{ number_format($item->quantity_received, 3) }}</td>
                                                <td class="px-4 py-2 text-sm">{{ $item->unit->name ?? '-' }}</td>
                                                <td class="px-4 py-2 text-sm">{{ $item->notes ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Receive Transfer Modal --}}
                @if($viewMode === 'receive' && $selectedTransfer)
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-bold text-gray-900">Receive Stock Transfer</h3>
                            <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div class="bg-blue-50 p-4 rounded">
                                <p class="text-sm text-blue-800">
                                    <strong>Transfer:</strong> {{ $selectedTransfer->reference_no }} |
                                    <strong>From:</strong> {{ $selectedTransfer->fromOutlet->name }} →
                                    <strong>To:</strong> {{ $selectedTransfer->toOutlet->name }}
                                </p>
                            </div>

                            <div class="border-t pt-4">
                                <h4 class="font-medium text-gray-900 mb-3">Receive Items (adjust quantities if needed)</h4>
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Item</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Qty Sent</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Qty to Receive</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Unit</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($selectedTransfer->items as $item)
                                            <tr>
                                                <td class="px-4 py-2 text-sm">{{ $item->item->name ?? 'N/A' }}</td>
                                                <td class="px-4 py-2 text-sm">{{ number_format($item->quantity_sent, 3) }}</td>
                                                <td class="px-4 py-2">
                                                    <input type="number" step="0.001"
                                                        wire:model="receiveQuantities.{{ $item->id }}"
                                                        class="w-24 border-gray-300 rounded-md shadow-sm text-sm">
                                                </td>
                                                <td class="px-4 py-2 text-sm">{{ $item->unit->name ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="flex justify-end gap-3 border-t pt-4">
                                <button wire:click="closeModal" type="button" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button wire:click="receiveTransfer" type="button" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md">
                                    Confirm Receipt
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    @endif
</div>
