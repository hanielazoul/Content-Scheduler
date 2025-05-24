@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Scheduled Posts</h5>
                <a href="{{ route('posts.create') }}" class="btn btn-primary btn-sm">New Post</a>
            </div>
            <div class="card-body">
                <!-- Filter Form -->
                <form action="{{ route('dashboard') }}" method="GET" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Search posts..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                                <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="platform" class="form-select">
                                <option value="">All Platforms</option>
                                @foreach($platforms as $platform)
                                    <option value="{{ $platform->id }}" {{ request('platform') == $platform->id ? 'selected' : '' }}>
                                        {{ $platform->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
                                <a href="{{ route('dashboard') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Platforms</th>
                                <th>Scheduled For</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($posts as $post)
                            <tr>
                                <td>{{ $post->title }}</td>
                                <td>
                                    @foreach($post->platforms as $platform)
                                        <span class="badge bg-primary">{{ $platform->name }}</span>
                                    @endforeach
                                </td>
                                <td>{{ $post->scheduled_time?->format('M d, Y H:i') }}</td>
                                <td>
                                    <span class="badge bg-{{ $post->status === 'published' ? 'success' : ($post->status === 'scheduled' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($post->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('posts.edit', $post) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                        <form action="{{ route('posts.destroy', $post) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $posts->links() }}
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Post Analytics</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>Posts by Status</h6>
                    <div class="progress">
                        <div class="progress-bar bg-success" style="width: {{ $analytics['published_percentage'] }}%">
                            Published ({{ $analytics['published_count'] }})
                        </div>
                        <div class="progress-bar bg-warning" style="width: {{ $analytics['scheduled_percentage'] }}%">
                            Scheduled ({{ $analytics['scheduled_count'] }})
                        </div>
                        <div class="progress-bar bg-secondary" style="width: {{ $analytics['draft_percentage'] }}%">
                            Draft ({{ $analytics['draft_count'] }})
                        </div>
                    </div>
                </div>
                <div>
                    <h6>Posts by Platform</h6>
                    <canvas id="platformChart"></canvas>
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
    const ctx = document.getElementById('platformChart').getContext('2d');
    new Chart(ctx, {
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
});
</script>
@endpush
