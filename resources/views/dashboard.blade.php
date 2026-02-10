@extends('layouts.app')

@section('content')
    <div class="text-center">
        <h2>Добро пожаловать!</h2>
        <a href="{{ route('domains.index') }}" class="btn btn-primary btn-lg mt-4">Перейти к моим доменам</a>
    </div>
    <div class="mt-5 card shadow">
    <div class="card-header bg-info text-white">
        <h5>Автоматические проверки</h5>
        <h4>Чекбокс нерабочий. Планировщик Schedule не добавляю чтобы не спамить бесконечными проверками, плюс это надо дорабатывать</h4>
    </div>
    <div class="card-body">
        
    

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="enable" id="autoCheck" {{ auth()->user()->auto_check_active ? 'checked' : '' }}>
                <label class="form-check-label" for="autoCheck">
                    Включить автопроверки на 60 минут
                </label>
            </div>

            <!-- @if(auth()->user()->auto_check_enabled_until)
                <p class="text-muted small">
                    Действует до: {{ auth()->user()->auto_check_enabled_until->format('H:i d.m.Y') }}
                </p>
            @endif -->

            <button type="submit" class="btn btn-primary">Сохранить</button>

    </div>
</div>
@endsection