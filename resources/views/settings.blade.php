@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Platform Settings</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <h6>Active Platforms</h6>
                        <p class="text-muted">Select the platforms you want to use for posting content.</p>
                        
                        <div class="row">
                            @foreach($platforms as $platform)
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="active_platforms[]" 
                                                   value="{{ $platform->id }}"
                                                   id="platform{{ $platform->id }}"
                                                   {{ in_array($platform->id, $activePlatforms) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="platform{{ $platform->id }}">
                                                {{ $platform->name }}
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            @switch($platform->type)
                                                @case('twitter')
                                                    Character limit: 280
                                                    @break
                                                @case('instagram')
                                                    Image required
                                                    @break
                                                @case('linkedin')
                                                    Professional content recommended
                                                    @break
                                                @case('facebook')
                                                    No specific restrictions
                                                    @break
                                            @endswitch
                                        </small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Post Analytics</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Posts by Platform</h6>
                        <canvas id="platformChart"></canvas>
                    </div>
                    <div class="col-md-6">
                        <h6>Success Rate</h6>
                        <canvas id="successRateChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Platform distribution chart
    const platformCtx = document.getElementById('platformChart').getContext('2d');
    new Chart(platformCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode($analytics['platforms']->pluck('name')) !!},
            datasets: [{
                data: {!! json_encode($analytics['platforms']->pluck('posts_count')) !!},
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0'
                ]
            }]
        }
    });

    // Success rate chart
    const successCtx = document.getElementById('successRateChart').getContext('2d');
    new Chart(successCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($analytics['platforms']->pluck('name')) !!},
            datasets: [{
                label: 'Success Rate',
                data: {!! json_encode($analytics['success_rates']->values()) !!},
                backgroundColor: '#36A2EB'
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush 