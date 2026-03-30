<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Housekeeping Tasks</h4>
            <p class="text-muted mb-0">Manage room cleaning and maintenance tasks</p>
        </div>
        <button wire:click="openModal" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Create Task
        </button>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Hotel Selection -->
    @if($hotels->count() > 1)
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <label class="form-label">Select Hotel</label>
                <select wire:model.live="selectedHotel" class="form-select">
                    @foreach($hotels as $hotel)
                        <option value="{{ $hotel->id }}">{{ $hotel->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif

    @if($selectedHotel)
        <!-- Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-start border-warning border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-warning text-white me-3">
                                <i class="ti ti-clock"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Pending Tasks</h6>
                                <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-start border-info border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-info text-white me-3">
                                <i class="ti ti-loader"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">In Progress</h6>
                                <h3 class="mb-0">{{ $stats['in_progress'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-start border-success border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-success text-white me-3">
                                <i class="ti ti-check"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Completed Today</h6>
                                <h3 class="mb-0">{{ $stats['completed_today'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Filter by Status</label>
                        <select wire:model.live="statusFilter" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Filter by Priority</label>
                        <select wire:model.live="priorityFilter" class="form-select">
                            <option value="">All Priorities</option>
                            <option value="urgent">Urgent</option>
                            <option value="high">High</option>
                            <option value="normal">Normal</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tasks Kanban Board -->
        <div class="row g-3">
            @foreach(['pending' => 'warning', 'in_progress' => 'info', 'completed' => 'success'] as $status => $color)
                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-{{ $color }} text-white">
                            <h6 class="mb-0">
                                <i class="ti ti-{{ $status === 'pending' ? 'clock' : ($status === 'in_progress' ? 'loader' : 'check-circle') }} me-2"></i>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                                ({{ $tasks->where('status', $status)->count() }})
                            </h6>
                        </div>
                        <div class="card-body p-2" style="max-height: 600px; overflow-y: auto;">
                            @foreach($tasks->where('status', $status) as $task)
                                <div class="card mb-2">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-1">Room {{ $task->room->number }}</h6>
                                                <small class="text-muted">{{ $task->room->roomType->name }}</small>
                                            </div>
                                            @php
                                                $priorityColors = [
                                                    'urgent' => 'danger',
                                                    'high' => 'warning',
                                                    'normal' => 'info',
                                                    'low' => 'secondary',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $priorityColors[$task->priority] ?? 'secondary' }}">
                                                {{ ucfirst($task->priority) }}
                                            </span>
                                        </div>

                                        <div class="mb-2">
                                            <span class="badge bg-light text-dark">{{ ucfirst(str_replace('_', ' ', $task->task_type)) }}</span>
                                        </div>

                                        @if($task->assignedTo)
                                            <div class="mb-2">
                                                <small class="text-muted">
                                                    <i class="ti ti-user me-1"></i>{{ $task->assignedTo->name }}
                                                </small>
                                            </div>
                                        @endif

                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="ti ti-calendar me-1"></i>{{ $task->scheduled_date->format('M d, Y') }}
                                            </small>
                                        </div>

                                        @if($task->notes)
                                            <div class="mb-2">
                                                <small class="text-muted">{{ Str::limit($task->notes, 50) }}</small>
                                            </div>
                                        @endif

                                        <!-- Action Buttons -->
                                        <div class="btn-group w-100" role="group">
                                            @if($task->status === 'pending')
                                                <button wire:click="updateTaskStatus('{{ $task->id }}', 'in_progress')" class="btn btn-sm btn-outline-info">
                                                    <i class="ti ti-player-play"></i> Start
                                                </button>
                                            @elseif($task->status === 'in_progress')
                                                <button wire:click="updateTaskStatus('{{ $task->id }}', 'completed')" class="btn btn-sm btn-outline-success">
                                                    <i class="ti ti-check"></i> Complete
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            @if($tasks->where('status', $status)->count() === 0)
                                <div class="text-center py-4">
                                    <i class="ti ti-inbox fs-2 text-muted"></i>
                                    <p class="text-muted mb-0">No {{ $status }} tasks</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $tasks->links() }}
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ti ti-bed fs-1 text-muted"></i>
                <h5 class="mt-3">No Hotel Selected</h5>
                <p class="text-muted">Please select a hotel to manage housekeeping tasks</p>
            </div>
        </div>
    @endif

    <!-- Create Task Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-vacuum-cleaner me-2"></i>
                            Create Housekeeping Task
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="createTask">
                            <div class="mb-3">
                                <label class="form-label">Room <span class="text-danger">*</span></label>
                                <select wire:model="room_id" class="form-select @error('room_id') is-invalid @enderror">
                                    <option value="">-- Select Room --</option>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}">Room {{ $room->number }} - {{ $room->roomType->name }}</option>
                                    @endforeach
                                </select>
                                @error('room_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Task Type <span class="text-danger">*</span></label>
                                <select wire:model="task_type" class="form-select">
                                    <option value="cleaning">Regular Cleaning</option>
                                    <option value="deep_cleaning">Deep Cleaning</option>
                                    <option value="turndown_service">Turndown Service</option>
                                    <option value="special_request">Special Request</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Priority <span class="text-danger">*</span></label>
                                <select wire:model="priority" class="form-select">
                                    <option value="normal">Normal</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                    <option value="low">Low</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Assign To</label>
                                <select wire:model="assigned_to" class="form-select">
                                    <option value="">-- Unassigned --</option>
                                    @foreach($staff as $member)
                                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Scheduled Date <span class="text-danger">*</span></label>
                                <input type="date" wire:model="scheduled_date" class="form-control @error('scheduled_date') is-invalid @enderror">
                                @error('scheduled_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea wire:model="notes" class="form-control" rows="2" placeholder="Any special instructions..."></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            <i class="ti ti-x me-1"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="createTask">
                            <i class="ti ti-device-floppy me-1"></i> Create Task
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
