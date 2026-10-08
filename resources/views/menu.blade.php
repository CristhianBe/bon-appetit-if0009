@extends('layouts.app')

@section('title', 'Menú')

@section('content')
<h2 style="text-align: center; color: var(--color-primary); margin-bottom: var(--space-md);">Nuestra Carta</h2>

<div class="catalog-grid">
    <div class="card">
        <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=500&q=80" alt="Entrada Gourmet" class="card-img">
        <div class="card-body">
            <h3 class="card-title">Ensalada Gourmet</h3>
            <p style="font-size: var(--text-sm); color: var(--color-text-muted); margin-bottom: var(--space-xs);">
                Ingredientes frescos de estación con aderezo de la casa.
            </p>
            <span class="card-price">₡8,500</span>
            <button class="btn-card">Ordenar</button>
        </div>
    </div>

    <div class="card">
        <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=500&q=80" alt="Plato Fuerte" class="card-img">
        <div class="card-body">
            <h3 class="card-title">Plato Fuerte Especial</h3>
            <p style="font-size: var(--text-sm); color: var(--color-text-muted); margin-bottom: var(--space-xs);">
                Corte premium acompañado de vegetales salteados.
            </p>
            <span class="card-price">₡14,000</span>
            <button class="btn-card">Ordenar</button>
        </div>
    </div>

    <div class="card">
        <img src="https://images.unsplash.com/photo-1551024709-8f23befc6f87?auto=format&fit=crop&w=500&q=80" alt="Postre Artesanal" class="card-img">
        <div class="card-body">
            <h3 class="card-title">Postre Artesanal</h3>
            <p style="font-size: var(--text-sm); color: var(--color-text-muted); margin-bottom: var(--space-xs);">
                Deliciosa combinación dulce para finalizar tu cena.
            </p>
            <span class="card-price">₡4,500</span>
            <button class="btn-card">Ordenar</button>
        </div>
    </div>
</div>
@endsection