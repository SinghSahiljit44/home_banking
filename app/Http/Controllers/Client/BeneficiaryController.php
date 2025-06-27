<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Beneficiary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BeneficiaryController extends Controller
{
    /**
     * Mostra tutti i beneficiari dell'utente
     */
    public function index()
    {
        $beneficiaries = Auth::user()
            ->beneficiaries()
            ->orderBy('is_favorite', 'desc')
            ->orderBy('name')
            ->get();

        return view('client.beneficiaries.index', compact('beneficiaries'));
    }

    /**
     * Salva un nuovo beneficiario
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'iban' => 'required|string|size:27|regex:/^[A-Z]{2}[0-9]{2}[A-Z0-9]{4}[0-9]{7}([A-Z0-9]?){0,16}$/',
            'bank_name' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
            'is_favorite' => 'boolean'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Verifica che l'IBAN non sia già presente per questo utente
        $exists = Auth::user()
            ->beneficiaries()
            ->where('iban', strtoupper(str_replace(' ', '', $request->iban)))
            ->exists();

        if ($exists) {
            return back()->withErrors(['iban' => 'Questo beneficiario è già presente nella tua lista.'])->withInput();
        }

        Auth::user()->beneficiaries()->create([
            'name' => $request->name,
            'iban' => strtoupper(str_replace(' ', '', $request->iban)),
            'bank_name' => $request->bank_name,
            'notes' => $request->notes,
            'is_favorite' => $request->boolean('is_favorite')
        ]);

        return back()->with('success', 'Beneficiario aggiunto con successo.');
    }

    /**
     * Aggiorna un beneficiario
     */
    public function update(Request $request, Beneficiary $beneficiary)
    {
        // Verifica che il beneficiario appartenga all'utente autenticato
        if ($beneficiary->user_id !== Auth::id()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'iban' => 'required|string|size:27|regex:/^[A-Z]{2}[0-9]{2}[A-Z0-9]{4}[0-9]{7}([A-Z0-9]?){0,16}$/',
            'bank_name' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
            'is_favorite' => 'boolean'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $beneficiary->update([
            'name' => $request->name,
            'iban' => strtoupper(str_replace(' ', '', $request->iban)),
            'bank_name' => $request->bank_name,
            'notes' => $request->notes,
            'is_favorite' => $request->boolean('is_favorite')
        ]);

        return back()->with('success', 'Beneficiario aggiornato con successo.');
    }

    /**
     * Cambia lo stato preferito di un beneficiario
     */
    public function toggleFavorite(Beneficiary $beneficiary)
    {
        if ($beneficiary->user_id !== Auth::id()) {
            abort(403);
        }

        $beneficiary->update([
            'is_favorite' => !$beneficiary->is_favorite
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Elimina un beneficiario
     */
    public function destroy(Beneficiary $beneficiary)
    {
        if ($beneficiary->user_id !== Auth::id()) {
            abort(403);
        }

        $beneficiary->delete();

        return response()->json(['success' => true]);
    }
}
