@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Мои домены</h4>

                        <a href="{{ config('app.telegram_channel_link') }}"
                            target="_blank"
                            class="btn btn-warning btn-sm me-2 fw-bold">
                                Логи проверок в Telegram
                            </a>

                            <form action="{{ route('domains.check-now') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm fw-bold">
                                    Запустить проверку сейчас
                                </button>
                            </form>

                    <a href="{{ route('domains.create') }}" class="btn btn-light btn-sm" style="text-decoration: none;">+ Добавить домен</a>
                </div>

                <div class="card-body">
                    @if($domains->isEmpty())
                        <div class="alert alert-info text-center">
                            Пока доменов нет. Добавьте первый!
                        </div>
                    @else
                        <table class="table table-hover table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>Домен</th>
                                    <th>Интервал (мин)</th>
                                    <th>Метод</th>
                                    <th>Таймаут (с)</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($domains as $domain)
                                    <tr>
                                        <td>{{ $domain->domain_name }}</td>
                                        <td>{{ $domain->check_interval }}</td>
                                        <td>{{ $domain->method }}</td>
                                        <td>{{ $domain->timeout }}</td>
                                        <td>
                                            <form action="{{ route('domains.destroy', $domain) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Удалить домен {{ $domain->domain_name }}?')">Удалить</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection