<?php

namespace App\Http\Requests;

use App\Models\Reserva;
use App\Services\ReservaService;
use Illuminate\Foundation\Http\FormRequest;

class ReservaStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'correo' => ['nullable', 'email', 'max:120'],
            'personas' => ['required', 'integer', 'min:1', 'max:50'],
            'zona' => ['nullable', 'string', 'max:40'],
            'fecha' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.ReservaService::hoy()],
            'hora' => ['required', 'date_format:H:i'],
            'mesa_id' => ['nullable', 'integer', 'exists:mesas,id'],
            'estado' => ['sometimes', 'in:'.implode(',', Reserva::ESTADOS)],
            'notas' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la reserva es obligatorio.',
            'correo.email' => 'El correo no tiene un formato válido.',
            'personas.required' => 'Indique cuántas personas asistirán.',
            'personas.min' => 'Debe asistir al menos una persona.',
            'fecha.required' => 'La fecha es obligatoria.',
            'fecha.date_format' => 'La fecha debe tener el formato AAAA-MM-DD.',
            'fecha.after_or_equal' => 'La fecha de la reserva no puede ser anterior a hoy.',
            'hora.required' => 'La hora es obligatoria.',
            'hora.date_format' => 'La hora debe tener el formato HH:MM.',
            'mesa_id.exists' => 'La mesa indicada no existe.',
            'estado.in' => 'El estado de la reserva no es válido.',
        ];
    }
}
