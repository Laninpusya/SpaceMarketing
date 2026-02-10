@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Проверьте ошибки:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Добавить домен</h4>
                </div>

                <div class="card-body">
                <form method="POST" action="{{ route('domains.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Домен (например, https://example.com)</label>
                    <input type="text" name="domain_name" class="form-control @error('domain_name') is-invalid @enderror" value="{{ old('domain_name') }}" required>
                    @error('domain_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Интервал проверки (минуты)</label>
                        <input type="number" name="check_interval" value="{{ old('check_interval', 5) }}" class="form-control @error('check_interval') is-invalid @enderror" min="1" required>
                        @error('check_interval')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Таймаут (секунды)</label>
                        <input type="number" name="timeout" value="{{ old('timeout', 10) }}" class="form-control @error('timeout') is-invalid @enderror" min="5" required>
                        @error('timeout')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Метод</label>
                        <select name="method" class="form-select @error('method') is-invalid @enderror">
                            <option value="HEAD" {{ old('method') == 'HEAD' ? 'selected' : '' }}>HEAD</option>
                            <option value="GET" {{ old('method') == 'GET' ? 'selected' : '' }}>GET</option>
                        </select>
                        @error('method')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-success">Сохранить</button>
                <a href="{{ route('domains.index') }}" class="btn btn-secondary">Отмена</a>
            </form>
                </div>
            </div>
        </div>
    </div>
@endsection