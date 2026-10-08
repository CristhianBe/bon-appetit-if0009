@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
<section style="text-align: center; padding: var(--space-xl) var(--space-sm);">
    <h2 style="font-size: var(--text-3xl); color: var(--color-primary);">Bienvenido a Bon Appétit</h2>
    <p style="color: var(--color-text-muted); max-width: 600px; margin: var(--space-sm) auto var(--space-md);">
        Una experiencia gastronómica única donde la tradición y la alta cocina se encuentran.
    </p>
    <a href="{{ route('menu') }}" class="btn-card" style="display: inline-block; text-decoration: none;">Ver nuestro menú</a>
</section>
@endsection