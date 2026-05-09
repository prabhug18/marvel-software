@extends('layouts.backend')

@section('content')
    <div class="toggle-btn" id="toggleBtn">
        <i class="fas fa-bars"></i>
    </div>
  
    @include('backend.include.mnubar')
  
    <main class="main-content" id="mainContent">
        @include('backend.include.header')       
        
        <div class="container-fluid px-3">
            <div class="row mt-4">
                <div class="col-md-8">
                    <!-- Lead Core Info -->
                    <div class="card shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-primary fw-bold">{{ $heading }}</h5>
                            <div class="d-flex gap-2">
                                @if($lead->status !== 'converted')
                                <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#followUpModal">
                                    <i class="fas fa-plus me-2"></i>Add Follow-up
                                </button>
                                <form id="convertLeadForm" action="{{ route('leads.convertToCustomer', $lead->id) }}" method="POST">
                                    @csrf
                                    <button type="button" class="btn btn-success rounded-pill px-4" id="convertLeadBtn">
                                        <i class="fas fa-user-check me-2"></i>Convert to Customer
                                    </button>
                                </form>
                                @else
                                <span class="badge bg-success px-4 py-2 rounded-pill fs-6"><i class="fas fa-check-circle me-2"></i>Converted to Customer</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="text-muted small text-uppercase fw-bold d-block">Contact Details</label>
                                    <p class="fs-5 mb-0 fw-bold">{{ $lead->name }}</p>
                                    <p class="text-primary fw-bold mb-0"><i class="fas fa-phone-alt me-2"></i>{{ $lead->mobile_no }}</p>
                                    <p class="text-muted small mb-0"><i class="fas fa-envelope me-2"></i>{{ $lead->email ?? 'No email provided' }}</p>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <label class="text-muted small text-uppercase fw-bold d-block">Expected Value</label>
                                    <h3 class="text-success fw-bold mb-0">₹{{ number_format($lead->expected_value ?? 0, 2) }}</h3>
                                    <span class="badge {{ $lead->priority == 'high' ? 'bg-danger' : ($lead->priority == 'medium' ? 'bg-warning text-dark' : 'bg-success') }} px-3 py-1 mt-1">
                                        {{ ucfirst($lead->priority) }} Priority
                                    </span>
                                </div>
                                <div class="col-md-12"><hr class="my-0 opacity-10"></div>
                                <div class="col-md-6">
                                    <label class="text-muted small text-uppercase fw-bold d-block">Location & Source</label>
                                    <p class="mb-1"><i class="fas fa-map-marker-alt me-2 text-danger"></i>{{ $lead->city ? $lead->city->name : '' }}, {{ $lead->state ? $lead->state->name : '' }}</p>
                                    <p class="mb-0"><i class="fas fa-bullhorn me-2 text-info"></i>Source: <strong>{{ $lead->source ?? 'Unknown' }}</strong></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small text-uppercase fw-bold d-block">Schedule</label>
                                    <p class="mb-0">Next Follow-up: 
                                        @if($lead->next_follow_up)
                                            <strong class="{{ \Carbon\Carbon::parse($lead->next_follow_up)->isPast() ? 'text-danger' : 'text-primary' }}">
                                                {{ \Carbon\Carbon::parse($lead->next_follow_up)->format('d M Y') }}
                                            </strong>
                                        @else
                                            <span class="text-muted italic">Not scheduled</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="col-md-12">
                                    <label class="text-muted small text-uppercase fw-bold d-block">Requirement Details</label>
                                    <div class="bg-light p-3 rounded-3 mt-1">
                                        <p class="mb-1"><strong>Products:</strong> {{ $lead->product_interest ?: 'General Enquiry' }}</p>
                                        <p class="mb-0"><strong>Brands:</strong> {{ $lead->brand_interest ?: 'Any' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Follow-up Timeline -->
                    <div class="card shadow-sm rounded-4 border-0">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-history me-2"></i>Follow-up History</h6>
                        </div>
                        <div class="card-body p-0">
                            @if($lead->followUps->count() > 0)
                                <div class="timeline p-4">
                                    @foreach($lead->followUps()->orderBy('follow_up_date', 'desc')->get() as $fu)
                                        <div class="timeline-item border-start border-2 border-primary position-relative ps-4 pb-4">
                                            <div class="timeline-dot bg-primary position-absolute rounded-circle" style="width: 12px; height: 12px; left: -7px; top: 0;"></div>
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="fw-bold">{{ \Carbon\Carbon::parse($fu->follow_up_date)->format('d M Y') }}</span>
                                                <span class="badge {{ $fu->outcome == 'interested' ? 'bg-success' : ($fu->outcome == 'not_interested' ? 'bg-danger' : 'bg-info') }}">
                                                    {{ ucfirst(str_replace('_', ' ', $fu->outcome)) }}
                                                </span>
                                            </div>
                                            <p class="mb-1 text-dark">{{ $fu->notes }}</p>
                                            @if($fu->next_follow_up)
                                                <p class="small text-muted mb-0"><i class="far fa-calendar-check me-1"></i>Next: {{ \Carbon\Carbon::parse($fu->next_follow_up)->format('d M Y') }}</p>
                                            @endif
                                            <p class="small text-muted mb-0">By: {{ $fu->user->name }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-5 text-center text-muted">
                                    <i class="fas fa-comment-slash fa-3x mb-3 opacity-25"></i>
                                    <p>No follow-up interactions recorded yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Status & Assignment -->
                    <div class="card shadow-sm rounded-4 border-0 mb-4">
                        <div class="card-body p-4">
                            <h6 class="text-muted small text-uppercase fw-bold mb-3">Current Status</h6>
                            <div class="d-flex align-items-center mb-4">
                                @php
                                    $statusIcons = [
                                        'new' => ['icon' => 'fa-star', 'color' => 'bg-info'],
                                        'follow_up' => ['icon' => 'fa-comments', 'color' => 'bg-primary'],
                                        'negotiation' => ['icon' => 'fa-handshake', 'color' => 'bg-warning'],
                                        'converted' => ['icon' => 'fa-trophy', 'color' => 'bg-success'],
                                        'lost' => ['icon' => 'fa-thumbs-down', 'color' => 'bg-secondary'],
                                    ];
                                    $s = $statusIcons[$lead->status] ?? $statusIcons['new'];
                                @endphp
                                <div class="{{ $s['color'] }} text-white p-3 rounded-circle me-3">
                                    <i class="fas {{ $s['icon'] }} fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0">{{ ucfirst(str_replace('_', ' ', $lead->status)) }}</h5>
                                    <p class="text-muted small mb-0">Last updated {{ $lead->updated_at->diffForHumans() }}</p>
                                </div>
                            </div>

                            <h6 class="text-muted small text-uppercase fw-bold mb-3">Owner Details</h6>
                            <div class="mb-3">
                                <label class="text-muted small d-block">Assigned Representative:</label>
                                <p class="fw-bold mb-0 text-dark"><i class="fas fa-id-badge me-2 text-primary"></i>{{ $lead->assignee ? $lead->assignee->name : 'Unassigned' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small d-block">Location:</label>
                                <p class="mb-0 fw-bold text-dark"><i class="fas fa-warehouse me-2 text-info"></i>{{ $lead->warehouse ? $lead->warehouse->name : 'Global' }}</p>
                            </div>
                            @if($lead->enquiry)
                            <div class="mb-0 p-2 bg-light rounded-3">
                                <label class="text-muted small d-block">Origin:</label>
                                <a href="{{ route('enquiries.show', $lead->enquiry_id) }}" class="small fw-bold">Linked to {{ $lead->enquiry->enquiry_number }}</a>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        @can('lead-edit')
                        <a href="{{ route('leads.edit', $lead->id) }}" class="btn btn-outline-warning rounded-pill py-2">
                            <i class="fas fa-edit me-2"></i>Edit Lead Info
                        </a>
                        @endcan
                        <a href="{{ route('leads.index') }}" class="btn btn-outline-secondary rounded-pill py-2">
                            <i class="fas fa-arrow-left me-2"></i>Back to Pipeline
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Follow-up Modal -->
    <div class="modal fade" id="followUpModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Log Follow-up</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('leads.addFollowUp', $lead->id) }}" method="POST">
                    @csrf
                    <div class="modal-body py-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Date</label>
                                <input type="date" class="form-control" name="follow_up_date" required value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Outcome</label>
                                <select name="outcome" class="form-select" required>
                                    <option value="callback">Callback Scheduled</option>
                                    <option value="interested">Very Interested</option>
                                    <option value="no_response">No Response</option>
                                    <option value="not_interested">Not Interested</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Discussion Notes</label>
                                <textarea class="form-control" name="notes" rows="3" required placeholder="What happened during this call/visit?"></textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-primary">Next Follow-up (Optional)</label>
                                <input type="date" class="form-control" name="next_follow_up">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Save Interaction</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            $('#convertLeadBtn').on('click', function() {
                Swal.fire({
                    title: 'Convert to Customer?',
                    text: 'Are you sure you want to promote this lead to a permanent customer record?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Convert It!',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'rounded-4 shadow-lg',
                        confirmButton: 'btn btn-success px-4 rounded-pill me-2',
                        cancelButton: 'btn btn-secondary px-4 rounded-pill'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#convertLeadForm').submit();
                    }
                });
            });
        });
    </script>
    @endpush
@endsection
