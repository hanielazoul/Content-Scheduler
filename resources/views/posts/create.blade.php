@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Create New Post</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <textarea class="form-control @error('content') is-invalid @enderror"
                                id="content" name="content" rows="4" required>{{ old('content') }}</textarea>
                            <div class="form-text">
                                <span id="charCount">0</span> characters
                                <span id="twitterWarning" class="text-danger d-none">
                                    (Twitter has a 280 character limit)
                                </span>
                            </div>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Image (Optional)</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror"
                                id="image" name="image" accept="image/*">
                            <div class="form-text">
                                Required for Instagram posts. Max size: 2MB
                            </div>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Platforms</label>
                            <div class="platform-checkboxes">
                                @foreach($platforms as $platform)
                                    <div class="form-check">
                                        <input class="form-check-input platform-checkbox"
                                            type="checkbox"
                                            name="platforms[]"
                                            value="{{ $platform->id }}"
                                            id="platform{{ $platform->id }}"
                                            {{ in_array($platform->id, old('platforms', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="platform{{ $platform->id }}">
                                            {{ $platform->name }}
                                            @if($platform->name === 'Twitter')
                                                <small class="text-muted">(280 character limit)</small>
                                            @elseif($platform->name === 'Instagram')
                                                <small class="text-muted">(Image required)</small>
                                            @endif
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('platforms')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="scheduled_time" class="form-label">Schedule Time</label>
                            <input type="text" class="form-control @error('scheduled_time') is-invalid @enderror"
                                id="scheduled_time" name="scheduled_time" value="{{ old('scheduled_time') }}" required>
                            @error('scheduled_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Schedule Post</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Flatpickr
        flatpickr("#scheduled_time", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            minDate: "today",
            time_24hr: true
        });

        // Character count and Twitter warning
        const content = document.getElementById('content');
        const charCount = document.getElementById('charCount');
        const twitterWarning = document.getElementById('twitterWarning');
        const twitterCheckbox = document.getElementById('platform1'); // Assuming Twitter is platform ID 1

        function updateCharCount() {
            const count = content.value.length;
            charCount.textContent = count;

            if (twitterCheckbox && twitterCheckbox.checked) {
                if (count > 280) {
                    twitterWarning.classList.remove('d-none');
                } else {
                    twitterWarning.classList.add('d-none');
                }
            }
        }

        content.addEventListener('input', updateCharCount);
        if (twitterCheckbox) {
            twitterCheckbox.addEventListener('change', updateCharCount);
        }

        // Instagram image requirement
        const instagramCheckbox = document.getElementById('platform2'); // Assuming Instagram is platform ID 2
        const imageInput = document.getElementById('image');

        if (instagramCheckbox) {
            instagramCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    imageInput.setAttribute('required', 'required');
                } else {
                    imageInput.removeAttribute('required');
                }
            });
        }
    });
</script>
@endpush
@endsection
